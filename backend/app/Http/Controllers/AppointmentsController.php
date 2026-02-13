<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointments\IndexRequest;
use App\Http\Requests\Appointments\StoreRequest;
use App\Http\Requests\Appointments\UpdateRequest;
use App\Mail\AppointmentRegistered;
use App\Mail\ClientVerifyEmail;
use App\Models\AuditLog;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use App\Queries\Appointments\AppointmentsIndexQuery;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AppointmentsController extends Controller
{
    public function index(IndexRequest $request, AppointmentsIndexQuery $appointmentsIndexQuery)
    {
        $filters = $request->filters();

        $built = $appointmentsIndexQuery->build($filters);

        $appointments = $built['query']
            ->paginate(20)
            ->withQueryString();

        $todayStart = CarbonImmutable::now()->startOfDay();
        $appointments->through(function (Appointment $appt) use ($todayStart) {
            $paidCents = (int) ($appt->paid_cents_sum ?? 0);
            $depositCents = (int) ($appt->deposit_cents ?? 0);
            $dueCents = max(0, ((int) ($appt->price_cents ?? 0)) - $depositCents);
            $remainingCents = max(0, $dueCents - $paidCents);

            $uiStatus = (string) ($appt->status ?? 'scheduled');

            if ($uiStatus === 'scheduled') {
                if (($paidCents > 0 || $depositCents > 0) && $remainingCents > 0) {
                    $uiStatus = 'pre_paid';
                } elseif ($remainingCents > 0 && $todayStart->greaterThan($appt->start_at->copy()->startOfDay())) {
                    $uiStatus = 'overdue';
                }
            }

            $appt->ui_status = $uiStatus;
            $appt->ui_status_label = [
                'scheduled' => 'Programado',
                'pre_paid' => 'Pago parcial',
                'overdue' => 'Atrasado',
                'paid' => 'Pagado',
                'cancelled' => 'Cancelado',
                'no_show' => 'No asistió',
            ][$uiStatus] ?? '—';

            $appt->ui_status_class = match ($uiStatus) {
                'paid' => 'bg-emerald-50 text-emerald-700',
                'scheduled' => 'bg-amber-50 text-amber-700',
                'pre_paid' => 'bg-blue-50 text-blue-700',
                'overdue' => 'bg-red-50 text-red-700',
                default => 'bg-slate-100 text-slate-700',
            };

            return $appt;
        });

        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes', 'price_cents']);

        $clients = Client::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(200)
            ->get(['id', 'first_name', 'last_name', 'phone']);

        $staff = User::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(200)
            ->get(['id', 'first_name', 'last_name']);

        return view('pages.appointments.index', [
            'appointments' => $appointments,
            'filters' => $filters,
            'weekStart' => $built['weekStart'],
            'weekEnd' => $built['weekEnd'],
            'services' => $services,
            'clients' => $clients,
            'staff' => $staff,
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->payload();

        $service = Service::query()->findOrFail($data['service_id']);

        $depositCents = max(0, (int) ($data['deposit_cents'] ?? 0));
        $servicePriceCents = (int) ($service->price_cents ?? 0);
        if ($depositCents > $servicePriceCents) {
            throw ValidationException::withMessages([
                'deposit' => 'El anticipo no puede ser mayor al precio del servicio.',
            ]);
        }

        $startAt = CarbonImmutable::parse($data['start_date'].' '.$data['start_time']);
        $endAt = $startAt->addMinutes((int) $service->duration_minutes);

        [$appointment, $client] = DB::transaction(function () use ($data, $service, $servicePriceCents, $depositCents, $startAt, $endAt) {
            $clientId = $data['client_id'];
            if (empty($clientId)) {
                $client = Client::create([
                    'dni' => $data['client_dni'],
                    'first_name' => $data['client_first_name'],
                    'last_name' => $data['client_last_name'],
                    'email' => $data['client_email'],
                    'email_verified_at' => null,
                    'phone' => $data['client_phone'],
                    'balance_cents' => 0,
                ]);
            } else {
                $client = Client::query()->lockForUpdate()->findOrFail((int) $clientId);
            }

            $clientBalanceBeforeCents = (int) ($client->balance_cents ?? 0);
            $debtIncreaseCents = max(0, $servicePriceCents - $depositCents);
            $client->update([
                'balance_cents' => $clientBalanceBeforeCents + $debtIncreaseCents,
            ]);

            $appointment = Appointment::create([
                'client_id' => (int) $client->id,
                'user_id' => (int) Auth::id(),
                'service_id' => (int) $service->id,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'price_cents' => $servicePriceCents,
                'deposit_cents' => $depositCents,
                'client_balance_before_cents' => $clientBalanceBeforeCents,
                'status' => 'scheduled',
                'notes' => $data['notes'],
            ]);

            AuditLog::record('create', Appointment::class, (int) $appointment->id, [
                'summary' => 'Alta de turno',
                'client_id' => (int) $client->id,
                'service_id' => (int) $service->id,
                'start_at' => $startAt->toDateTimeString(),
                'price' => number_format($servicePriceCents / 100, 2, ',', '.'),
                'deposit' => number_format($depositCents / 100, 2, ',', '.'),
            ]);

            return [$appointment, $client];
        });

        if ($client?->email) {
            try {
                $appointmentForMail = Appointment::query()
                    ->with(['client', 'service', 'user'])
                    ->find((int) $appointment->id);

                if ($appointmentForMail && $client->hasVerifiedEmail()) {
                    Mail::to($client->email)->send(new AppointmentRegistered($appointmentForMail));
                } elseif ($appointmentForMail) {
                    Mail::to($client->email)->send(new ClientVerifyEmail($client, (int) $appointmentForMail->id));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $week = isset($data['week']) ? trim((string) $data['week']) : '';

        return $week !== ''
            ? redirect()->to(route('appointments.index', ['week' => $week], false))
            : redirect()->to(route('appointments.index', [], false));
    }

    public function update(UpdateRequest $request, Appointment $appointment)
    {
        $data = $request->payload();

        $week = $data['week'] ?? null;

        DB::transaction(function () use ($data, $appointment) {
            $appointment = Appointment::query()
                ->lockForUpdate()
                ->withSum(['payments as paid_cents_sum' => function ($q) {
                    $q->whereIn('status', ['paid', 'partial']);
                }], 'amount_cents')
                ->findOrFail((int) $appointment->id);

            $hasPayments = ((int) ($appointment->paid_cents_sum ?? 0)) > 0;

            $oldClientId = (int) $appointment->client_id;
            $oldServiceId = (int) $appointment->service_id;

            $oldDepositCents = max(0, (int) ($appointment->deposit_cents ?? 0));
            $newDepositCents = max(0, (int) ($data['deposit_cents'] ?? 0));

            $now = CarbonImmutable::now();
            if ($appointment->start_at !== null && $now->greaterThan($appointment->start_at) && $oldDepositCents !== $newDepositCents) {
                throw ValidationException::withMessages([
                    'deposit' => 'No se puede cambiar el anticipo si el turno ya pasó su fecha/hora de inicio.',
                ]);
            }

            $newClientId = (int) $data['client_id'];
            $newServiceId = (int) $data['service_id'];

            if ($hasPayments && ($oldClientId !== $newClientId || $oldServiceId !== $newServiceId || $oldDepositCents !== $newDepositCents)) {
                throw ValidationException::withMessages([
                    'client_id' => 'No se puede cambiar el cliente, el servicio o el anticipo si el turno tiene pagos registrados.',
                ]);
            }

            $service = Service::query()->findOrFail($newServiceId);

            $newServicePriceCents = (int) ($service->price_cents ?? 0);
            if ($newDepositCents > $newServicePriceCents) {
                throw ValidationException::withMessages([
                    'deposit' => 'El anticipo no puede ser mayor al precio del servicio.',
                ]);
            }

            $startAt = CarbonImmutable::parse($data['start_date'].' '.$data['start_time']);
            $endAt = $startAt->addMinutes((int) $service->duration_minutes);

            $oldDebtIncreaseCents = max(0, ((int) ($appointment->price_cents ?? 0)) - $oldDepositCents);
            $newDebtIncreaseCents = max(0, $newServicePriceCents - $newDepositCents);

            if (! $hasPayments && ($oldClientId !== $newClientId || $oldDebtIncreaseCents !== $newDebtIncreaseCents)) {
                if ($oldClientId === $newClientId) {
                    $client = Client::query()->lockForUpdate()->findOrFail($oldClientId);
                    $delta = $newDebtIncreaseCents - $oldDebtIncreaseCents;
                    $client->update([
                        'balance_cents' => max(0, ((int) $client->balance_cents) + (int) $delta),
                    ]);
                } else {
                    $firstId = min($oldClientId, $newClientId);
                    $secondId = max($oldClientId, $newClientId);

                    $firstClient = Client::query()->lockForUpdate()->findOrFail($firstId);
                    $secondClient = Client::query()->lockForUpdate()->findOrFail($secondId);

                    $oldClient = $oldClientId === $firstId ? $firstClient : $secondClient;
                    $newClient = $newClientId === $firstId ? $firstClient : $secondClient;

                    $oldClient->update([
                        'balance_cents' => max(0, ((int) $oldClient->balance_cents) - $oldDebtIncreaseCents),
                    ]);
                    $newClient->update([
                        'balance_cents' => ((int) $newClient->balance_cents) + $newDebtIncreaseCents,
                    ]);
                }
            }

            $from = [
                'start_at' => optional($appointment->start_at)->toDateTimeString(),
                'client_id' => $oldClientId,
                'service_id' => $oldServiceId,
                'user_id' => (int) $appointment->user_id,
            ];

            $appointment->update([
                'client_id' => $newClientId,
                'service_id' => $newServiceId,
                'user_id' => (int) $data['user_id'],
                'start_at' => $startAt,
                'end_at' => $endAt,
                'price_cents' => $newServicePriceCents,
                'deposit_cents' => $newDepositCents,
            ]);

            AuditLog::record('appointment.update', Appointment::class, (int) $appointment->id, [
                'summary' => 'Actualizó turno',
                'from' => $from,
                'to' => [
                    'start_at' => $startAt->toDateTimeString(),
                    'client_id' => $newClientId,
                    'service_id' => $newServiceId,
                    'user_id' => (int) $data['user_id'],
                ],
            ]);
        });

        $week = $week !== null ? trim((string) $week) : '';

        return $week !== ''
            ? redirect()->to(route('appointments.index', ['week' => $week], false))
            : redirect()->to(route('appointments.index', [], false));
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $week = (string) ($request->input('week') ?? '');

        try {
            DB::transaction(function () use ($appointment) {
                $appointment = Appointment::query()
                    ->lockForUpdate()
                    ->withSum(['payments as paid_cents_sum' => function ($q) {
                        $q->whereIn('status', ['paid', 'partial']);
                    }], 'amount_cents')
                    ->findOrFail((int) $appointment->id);

                $hasPayments = ((int) ($appointment->paid_cents_sum ?? 0)) > 0;
                $hasDeposit = ((int) ($appointment->deposit_cents ?? 0)) > 0;

                if ($hasPayments || $hasDeposit) {
                    throw ValidationException::withMessages([
                        'delete' => 'No se puede borrar un turno si ya tiene un pago parcial/completo o anticipo registrado.',
                    ]);
                }

                $client = Client::query()->lockForUpdate()->findOrFail((int) $appointment->client_id);

                $debtIncreaseCents = max(0, ((int) ($appointment->price_cents ?? 0)) - ((int) ($appointment->deposit_cents ?? 0)));
                if ($debtIncreaseCents > 0) {
                    $client->update([
                        'balance_cents' => max(0, ((int) $client->balance_cents) - $debtIncreaseCents),
                    ]);
                }

                $appointmentId = (int) $appointment->id;
                $appointment->delete();

                AuditLog::record('appointment.delete', Appointment::class, $appointmentId, [
                    'summary' => 'Borró turno',
                    'client_id' => (int) $client->id,
                    'service_id' => (int) $appointment->service_id,
                    'start_at' => optional($appointment->start_at)->toDateTimeString(),
                ]);
            });
        } catch (ValidationException $e) {
            $week = trim((string) $week);
            return ($week !== ''
                ? redirect()->to(route('appointments.index', ['week' => $week], false))
                : redirect()->to(route('appointments.index', [], false)))
                ->withErrors($e->errors())
                ->withInput();
        }

        $week = trim((string) $week);
        return $week !== ''
            ? redirect()->to(route('appointments.index', ['week' => $week], false))
            : redirect()->to(route('appointments.index', [], false));
    }
}
