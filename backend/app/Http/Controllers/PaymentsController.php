<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\IndexRequest;
use App\Http\Requests\Payments\StoreRequest;
use App\Http\Requests\Payments\UpdateRequest;
use App\Models\AuditLog;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Payment;
use App\Queries\Payments\PaymentsIndexQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentsController extends Controller
{
    public function index(IndexRequest $request, PaymentsIndexQuery $paymentsIndexQuery)
    {
        $filters = $request->filters();

        $payments = $paymentsIndexQuery
            ->build($filters)
            ->paginate(20)
            ->withQueryString();

        $clients = Client::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(200)
            ->get(['id', 'first_name', 'last_name', 'phone']);

        $appointments = Appointment::query()
            ->with(['client', 'service'])
            ->withSum(['payments as paid_cents_sum' => function ($q) {
                $q->whereIn('status', ['paid', 'partial']);
            }], 'amount_cents')
            ->whereIn('status', ['scheduled', 'paid'])
            ->orderByDesc('start_at')
            ->limit(200)
            ->get(['id', 'client_id', 'service_id', 'start_at', 'price_cents', 'deposit_cents', 'status'])
            ->map(function (Appointment $appt) {
                $dueCents = max(0, ((int) $appt->price_cents) - ((int) $appt->deposit_cents));
                $paidCents = (int) ($appt->paid_cents_sum ?? 0);
                $appt->remaining_cents = max(0, $dueCents - $paidCents);
                return $appt;
            })
            ->filter(function (Appointment $appt) {
                return ((int) ($appt->remaining_cents ?? 0)) > 0;
            })
            ->values();

        return view('pages.payments.index', [
            'payments' => $payments,
            'filters' => $filters,
            'clients' => $clients,
            'appointments' => $appointments,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $payload = $request->payload();

        DB::transaction(function () use ($payload) {
            $clientId = $payload['client_id'];

            $appointmentId = $payload['appointment_id'] ?? null;
            $appointment = null;
            $appointmentRemainingCents = null;

            if (empty($clientId)) {
                if (! empty($appointmentId)) {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'Para asociar un turno, primero seleccioná un cliente existente.',
                    ]);
                }

                $client = Client::create([
                    'dni' => $payload['client_dni'] ?? null,
                    'first_name' => $payload['client_first_name'],
                    'last_name' => $payload['client_last_name'],
                    'email' => $payload['client_email'] ?? null,
                    'phone' => $payload['client_phone'],
                    'balance_cents' => 0,
                ]);

                $clientId = $client->id;
            }

            if (! empty($appointmentId)) {
                $appointment = Appointment::query()
                    ->lockForUpdate()
                    ->withSum(['payments as paid_cents_sum' => function ($q) {
                        $q->whereIn('status', ['paid', 'partial']);
                    }], 'amount_cents')
                    ->findOrFail($appointmentId);

                if ((int) $appointment->client_id !== (int) $clientId) {
                    throw ValidationException::withMessages([
                        'appointment_id' => 'El turno seleccionado no pertenece al cliente elegido.',
                    ]);
                }

                $dueCents = max(0, ((int) $appointment->price_cents) - ((int) $appointment->deposit_cents));
                $paidCents = (int) ($appointment->paid_cents_sum ?? 0);
                $appointmentRemainingCents = max(0, $dueCents - $paidCents);

                if (((int) $payload['amount_cents']) > $appointmentRemainingCents) {
                    throw ValidationException::withMessages([
                        'amount' => 'El monto supera el saldo pendiente del turno.',
                    ]);
                }
            }

            $paidAt = now();

            $paymentStatus = 'paid';
            if ($appointment && $appointmentRemainingCents !== null) {
                $paymentStatus = ((int) $payload['amount_cents']) < ((int) $appointmentRemainingCents) ? 'partial' : 'paid';
            }

            // paid_at always reflects when the payment was registered.

            $payment = Payment::create([
                'client_id' => $clientId,
                'appointment_id' => $appointmentId,
                'amount_cents' => (int) $payload['amount_cents'],
                'method' => $payload['method'],
                'status' => $paymentStatus,
                'paid_at' => $paidAt,
                'reference' => $payload['reference'] ?? null,
                'notes' => $payload['notes'] ?? null,
            ]);

            AuditLog::record('payment.create', Payment::class, (int) $payment->id, [
                'summary' => 'Registro de pago',
                'client_id' => (int) $clientId,
                'appointment_id' => $appointmentId ? (int) $appointmentId : null,
                'amount' => number_format(((int) $payload['amount_cents']) / 100, 2, ',', '.'),
                'method' => $payload['method'],
                'status' => $paymentStatus,
            ]);

            if (in_array($paymentStatus, ['partial', 'paid'], true)) {
                $client = Client::query()->lockForUpdate()->findOrFail($clientId);
                $newBalance = max(0, ((int) $client->balance_cents) - ((int) $payload['amount_cents']));
                $client->update(['balance_cents' => $newBalance]);

                if ($appointment && $appointmentRemainingCents !== null) {
                    $remainingAfter = max(0, ((int) $appointmentRemainingCents) - ((int) $payload['amount_cents']));
                    $from = (string) $appointment->status;
                    $to = null;

                    if ($remainingAfter === 0 && $appointment->status !== 'cancelled' && $appointment->status !== 'no_show') {
                        $to = 'paid';
                        $appointment->update(['status' => $to]);
                    } elseif ($remainingAfter > 0 && $appointment->status === 'paid') {
                        $to = 'scheduled';
                        $appointment->update(['status' => $to]);
                    }

                    if ($to !== null && $to !== $from) {
                        AuditLog::record('appointment.status_change', Appointment::class, (int) $appointment->id, [
                            'summary' => 'Cambio de estado de turno por pago',
                            'from' => $from,
                            'to' => $to,
                            'payment_id' => (int) $payment->id,
                        ]);
                    }
                }
            }
        });

        return redirect()->to(route('payments.index', [], false));
    }

    public function update(UpdateRequest $request, Payment $payment)
    {
        $payload = $request->payload();

        DB::transaction(function () use ($payload, $payment) {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail((int) $payment->id);

            $clientId = (int) $lockedPayment->client_id;
            $appointmentId = $lockedPayment->appointment_id ? (int) $lockedPayment->appointment_id : null;

            $oldAmountCents = (int) $lockedPayment->amount_cents;
            $oldStatus = (string) $lockedPayment->status;
            $newAmountCents = (int) $payload['amount_cents'];

            if ($newAmountCents <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'El monto debe ser mayor a 0.',
                ]);
            }

            $appointment = null;
            $remainingBeforeCents = null;
            $dueCents = null;
            $paidOthersCents = null;

            if ($appointmentId !== null) {
                $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointmentId);

                $dueCents = max(0, ((int) $appointment->price_cents) - ((int) $appointment->deposit_cents));
                $paidOthersCents = (int) Payment::query()
                    ->where('appointment_id', $appointmentId)
                    ->whereIn('status', ['paid', 'partial'])
                    ->where('id', '!=', (int) $lockedPayment->id)
                    ->sum('amount_cents');

                $remainingBeforeCents = max(0, $dueCents - $paidOthersCents);

                if ($newAmountCents > $remainingBeforeCents) {
                    throw ValidationException::withMessages([
                        'amount' => 'El monto supera el saldo pendiente del turno.',
                    ]);
                }
            }

            $newStatus = 'paid';
            if ($appointment && $remainingBeforeCents !== null) {
                $newStatus = $newAmountCents < $remainingBeforeCents ? 'partial' : 'paid';
            }

            $oldAffectsBalance = in_array($oldStatus, ['partial', 'paid'], true);
            $newAffectsBalance = in_array($newStatus, ['partial', 'paid'], true);

            $lockedPayment->update([
                'amount_cents' => $newAmountCents,
                'status' => $newStatus,
            ]);

            AuditLog::record('payment.update', Payment::class, (int) $lockedPayment->id, [
                'summary' => 'Edición de pago',
                'client_id' => $clientId,
                'appointment_id' => $appointmentId,
                'from_amount' => number_format($oldAmountCents / 100, 2, ',', '.'),
                'to_amount' => number_format($newAmountCents / 100, 2, ',', '.'),
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
            ]);

            // Adjust client balance by reverting old payment effect and applying the new one.
            $client = Client::query()->lockForUpdate()->findOrFail($clientId);
            $balance = (int) $client->balance_cents;
            if ($oldAffectsBalance) {
                $balance += $oldAmountCents;
            }
            if ($newAffectsBalance) {
                $balance -= $newAmountCents;
            }
            $client->update(['balance_cents' => max(0, $balance)]);

            if ($appointment && $dueCents !== null && $paidOthersCents !== null) {
                $paidNowCents = $paidOthersCents + ($newAffectsBalance ? $newAmountCents : 0);
                $remainingAfter = max(0, $dueCents - $paidNowCents);

                $from = (string) $appointment->status;
                $to = null;

                if ($remainingAfter === 0 && $appointment->status !== 'cancelled' && $appointment->status !== 'no_show') {
                    $to = 'paid';
                    $appointment->update(['status' => $to]);
                } elseif ($remainingAfter > 0 && $appointment->status === 'paid') {
                    $to = 'scheduled';
                    $appointment->update(['status' => $to]);
                }

                if ($to !== null && $to !== $from) {
                    AuditLog::record('appointment.status_change', Appointment::class, (int) $appointment->id, [
                        'summary' => 'Cambio de estado de turno por edición de pago',
                        'from' => $from,
                        'to' => $to,
                        'payment_id' => (int) $lockedPayment->id,
                    ]);
                }
            }
        });

        return redirect()->to(route('payments.index', [], false));
    }

    public function destroy(Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail((int) $payment->id);

            $paymentId = (int) $lockedPayment->id;
            $clientId = (int) $lockedPayment->client_id;
            $appointmentId = $lockedPayment->appointment_id ? (int) $lockedPayment->appointment_id : null;

            $amountCents = (int) $lockedPayment->amount_cents;
            $status = (string) $lockedPayment->status;
            $method = (string) $lockedPayment->method;

            $appointment = null;
            if ($appointmentId !== null) {
                $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointmentId);
            }

            if (in_array($status, ['partial', 'paid'], true)) {
                $client = Client::query()->lockForUpdate()->findOrFail($clientId);
                $client->update([
                    'balance_cents' => ((int) $client->balance_cents) + $amountCents,
                ]);
            }

            $lockedPayment->delete();

            AuditLog::record('payment.delete', Payment::class, $paymentId, [
                'summary' => 'Eliminación de pago',
                'client_id' => $clientId,
                'appointment_id' => $appointmentId,
                'amount' => number_format($amountCents / 100, 2, ',', '.'),
                'method' => $method,
                'status' => $status,
            ]);

            if ($appointment) {
                $dueCents = max(0, ((int) $appointment->price_cents) - ((int) $appointment->deposit_cents));
                $paidCents = (int) Payment::query()
                    ->where('appointment_id', (int) $appointment->id)
                    ->whereIn('status', ['paid', 'partial'])
                    ->sum('amount_cents');

                $remainingAfter = max(0, $dueCents - $paidCents);

                $from = (string) $appointment->status;
                $to = null;

                if ($remainingAfter === 0 && $appointment->status !== 'cancelled' && $appointment->status !== 'no_show') {
                    $to = 'paid';
                    $appointment->update(['status' => $to]);
                } elseif ($remainingAfter > 0 && $appointment->status === 'paid') {
                    $to = 'scheduled';
                    $appointment->update(['status' => $to]);
                }

                if ($to !== null && $to !== $from) {
                    AuditLog::record('appointment.status_change', Appointment::class, (int) $appointment->id, [
                        'summary' => 'Cambio de estado de turno por eliminación de pago',
                        'from' => $from,
                        'to' => $to,
                        'payment_id' => $paymentId,
                    ]);
                }
            }
        });

        return redirect()->to(route('payments.index', [], false));
    }

}
