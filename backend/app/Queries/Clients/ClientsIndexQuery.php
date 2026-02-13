<?php

namespace App\Queries\Clients;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;

class ClientsIndexQuery
{
    /** @param array{q:?string,has_debt:?bool} $filters */
    public function build(array $filters): Builder
    {
        $query = Client::query();

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $numericId = ctype_digit($term) ? (int) $term : null;

            $query->where(function (Builder $q) use ($term, $numericId) {
                $q->where('dni', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");

                if ($numericId !== null) {
                    $q->orWhere('id', $numericId);
                }
            });
        }

        if ($filters['has_debt'] === true) {
            $query->where('balance_cents', '>', 0);
        }

        return $query->orderBy('last_name')->orderBy('first_name');
    }
}
