<?php

namespace App\Queries\Appointments;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsIndexQuery
{
    /**
     * @param array{week:?string,q:?string,status:?string,user_id:?int} $filters
     * @return array{query:Builder,weekStart:?CarbonImmutable,weekEnd:?CarbonImmutable}
     */
    public function build(array $filters): array
    {
        $todayStart = CarbonImmutable::now()->startOfDay();

        $weekStart = null;
        $weekEnd = null;
        if (! empty($filters['week'])) {
            $anchor = CarbonImmutable::parse($filters['week']);
            $weekStart = $anchor->startOfWeek(CarbonImmutable::MONDAY)->startOfDay();
            // Spa works Monday–Saturday (no Sundays)
            $weekEnd = $weekStart->addDays(5)->endOfDay();
        }

        $query = Appointment::query()
            ->with(['client', 'service', 'user'])
            ->withSum(['payments as paid_cents_sum' => function (Builder $p) {
                $p->whereIn('status', ['paid', 'partial']);
            }], 'amount_cents')
            ->orderByDesc('start_at');

        if ($weekStart !== null && $weekEnd !== null) {
            $query->whereBetween('start_at', [$weekStart, $weekEnd]);
        } else {
            // Only show appointments from today onwards when not filtering by week.
            $query->where('start_at', '>=', $todayStart);
        }

        if (! empty($filters['status'])) {
            $status = $filters['status'];

            if (in_array($status, ['paid', 'cancelled', 'no_show'], true)) {
                $query->where('status', $status);
            } elseif ($status === 'pre_paid') {
                $query->where('status', 'scheduled')
                    ->where(function (Builder $q) {
                        $q->whereHas('payments', function (Builder $p) {
                            $p->whereIn('status', ['paid', 'partial']);
                        })->orWhere('deposit_cents', '>', 0);
                    });
            } elseif ($status === 'overdue') {
                $query->where('status', 'scheduled')
                    ->where('start_at', '<', $todayStart)
                    ->where('deposit_cents', 0)
                    ->whereDoesntHave('payments', function (Builder $p) {
                        $p->whereIn('status', ['paid', 'partial']);
                    });
            } elseif ($status === 'scheduled') {
                $query->where('status', 'scheduled')
                    ->where('start_at', '>=', $todayStart)
                    ->where('deposit_cents', 0)
                    ->whereDoesntHave('payments', function (Builder $p) {
                        $p->whereIn('status', ['paid', 'partial']);
                    });
            }
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $numericId = ctype_digit($term) ? (int) $term : null;

            $query->where(function (Builder $q) use ($term, $numericId) {
                if ($numericId !== null) {
                    $q->where('id', $numericId);
                } else {
                    $q->whereHas('client', function (Builder $c) use ($term) {
                        $c->where('dni', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    })->orWhereHas('service', function (Builder $s) use ($term) {
                        $s->where('name', 'like', "%{$term}%");
                    });

                    return;
                }

                $q->orWhereHas('client', function (Builder $c) use ($term) {
                    $c->where('dni', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                })->orWhereHas('service', function (Builder $s) use ($term) {
                    $s->where('name', 'like', "%{$term}%");
                });
            });
        }

        return [
            'query' => $query,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
        ];
    }
}
