<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;

$partials = Payment::query()
    ->where('status', 'partial')
    ->with([
        'client',
        'appointment' => function ($q) {
            /** @var Builder $q */
            $q->withSum(['payments as paid_cents_sum' => function (Builder $p) {
                $p->whereIn('status', ['paid', 'partial']);
            }], 'amount_cents');
        },
    ])
    ->orderByDesc('created_at')
    ->get();

echo 'partial payments: ' . $partials->count() . PHP_EOL;

foreach ($partials as $payment) {
    $remainingCents = null;
    $dueCents = null;
    $paidCents = null;

    $appointmentExists = $payment->appointment_id
        ? Appointment::query()->whereKey((int) $payment->appointment_id)->exists()
        : null;

    if ($payment->appointment) {
        $appt = $payment->appointment;
        $dueCents = max(0, ((int) ($appt->price_cents ?? 0)) - ((int) ($appt->deposit_cents ?? 0)));
        $paidCents = (int) ($appt->paid_cents_sum ?? 0);
        $remainingCents = max(0, $dueCents - $paidCents);
    }

    $clientBalanceCents = $payment->client ? (int) ($payment->client->balance_cents ?? 0) : null;

    $computedForButton = null;
    if ($payment->appointment) {
        $computedForButton = (int) $remainingCents;
    } elseif ($payment->client) {
        $computedForButton = max(0, (int) $clientBalanceCents);
    }

    $wouldShowButton = (
        $payment->status === 'partial'
        && ! empty($payment->client_id)
        && $computedForButton !== null
        && $computedForButton > 0
    ) ? 'yes' : 'no';

    echo implode(' | ', [
        'payment_id=' . $payment->id,
        'client_id=' . ($payment->client_id ?? 'null'),
        'appointment_id=' . ($payment->appointment_id ?? 'null'),
        'appointment_exists=' . ($appointmentExists === null ? 'null' : ($appointmentExists ? 'yes' : 'no')),
        'client_balance=' . ($clientBalanceCents === null ? 'null' : (string) $clientBalanceCents),
        'due=' . ($dueCents === null ? 'null' : (string) $dueCents),
        'paid=' . ($paidCents === null ? 'null' : (string) $paidCents),
        'remaining=' . ($remainingCents === null ? 'null' : (string) $remainingCents),
        'computed_for_button=' . ($computedForButton === null ? 'null' : (string) $computedForButton),
        'would_show_button=' . $wouldShowButton,
    ]) . PHP_EOL;
}
