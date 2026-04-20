<?php

namespace App\Queries\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UsersIndexQuery
{
    /** @param array{q:?string,role_id:?int} $filters */
    public function build(array $filters): Builder
    {
        /** @var Builder $query */
        $query = User::query()->with('role');

        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $numericId = ctype_digit($term) ? (int) $term : null;

            $query->where(function (Builder $q) use ($term, $numericId) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('job_title', 'like', "%{$term}%");

                if ($numericId !== null) {
                    $q->orWhere('id', $numericId);
                }
            });
        }

        if (! empty($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        return $query->orderBy('last_name')->orderBy('first_name');
    }
}
