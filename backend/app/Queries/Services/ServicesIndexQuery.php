<?php

namespace App\Queries\Services;

use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;

class ServicesIndexQuery
{
    /** @param array{q:?string,is_active:?bool} $filters */
    public function build(array $filters): Builder
    {
        $query = Service::query();

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $numericId = ctype_digit($term) ? (int) $term : null;

            $query->where(function (Builder $q) use ($term, $numericId) {
                $q->where('name', 'like', "%{$term}%");
                if ($numericId !== null) {
                    $q->orWhere('id', $numericId);
                }
            });
        }

        if ($filters['is_active'] !== null) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name');
    }
}
