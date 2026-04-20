<?php

namespace App\Http\Requests\Clients;

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
            'has_debt' => ['nullable', 'boolean'],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'q' => isset($validated['q']) ? trim((string) $validated['q']) : null,
            'has_debt' => array_key_exists('has_debt', $validated)
                ? filter_var($validated['has_debt'], FILTER_VALIDATE_BOOL)
                : null,
        ];
    }
}
