<?php

namespace App\Queries\Payments;

use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentsIndexQuery
{
    /** @param array{q:?string,status:?string,method:?string,from:?string,to:?string} $filters */
    public function build(array $filters): Builder
    {
        /** @var Builder $query */
        $query = Payment::query()->with([
            'client',
            'appointment' => function (BelongsTo $appointment) {
                $appointment->withSum(['payments as paid_cents_sum' => function (Builder $p) {
                    $p->whereIn('status', ['paid', 'partial']);
                }], 'amount_cents');
            },
        ]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (! empty($filters['from'])) {
            $from = CarbonImmutable::parse($filters['from'])->startOfDay();
            $query->where('created_at', '>=', $from);
        }

        if (! empty($filters['to'])) {
            $to = CarbonImmutable::parse($filters['to'])->endOfDay();
            $query->where('created_at', '<=', $to);
        }

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $numericId = ctype_digit($term) ? (int) $term : null;

            $query->where(function (Builder $q) use ($term, $numericId) {
                $q->whereHas('client', function (Builder $c) use ($term) {
                    $c->where('dni', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });

                if ($numericId !== null) {
                    $q->orWhere('id', $numericId);
                }
            });
        }

        return $query->latest('created_at');
    }
}
