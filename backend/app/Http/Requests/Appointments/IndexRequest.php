<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Week anchor date (any date inside the week). We normalize to Monday-Sunday.
            'week' => ['nullable', 'date'],

            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:scheduled,pre_paid,overdue,paid,cancelled,no_show'],
            'user_id' => ['nullable', 'integer'],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'week' => $validated['week'] ?? null,
            'q' => isset($validated['q']) ? trim((string) $validated['q']) : null,
            'status' => $validated['status'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
        ];
    }
}
