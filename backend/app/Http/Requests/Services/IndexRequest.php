<?php

namespace App\Http\Requests\Services;

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
            'is_active' => ['nullable', 'in:0,1'],
        ];
    }

    public function filters(): array
    {
        $v = $this->validated();

        return [
            'q' => isset($v['q']) ? trim((string) $v['q']) : null,
            'is_active' => array_key_exists('is_active', $v) ? (bool) ((int) $v['is_active']) : null,
        ];
    }
}
