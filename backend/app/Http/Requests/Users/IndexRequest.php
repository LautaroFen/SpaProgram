<?php

namespace App\Http\Requests\Users;

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
            'q' => ['nullable', 'string', 'max:100'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'role_q' => ['nullable', 'string', 'max:50'],
        ];
    }

    /** @return array{q:?string,role_id:?int,role_q:?string} */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'q' => isset($validated['q']) ? trim((string) $validated['q']) : null,
            'role_id' => isset($validated['role_id']) ? (int) $validated['role_id'] : null,
            'role_q' => isset($validated['role_q']) ? trim((string) $validated['role_q']) : null,
        ];
    }
}
