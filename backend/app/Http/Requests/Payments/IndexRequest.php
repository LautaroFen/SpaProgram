<?php

namespace App\Http\Requests\Payments;

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
            'status' => ['nullable', 'in:partial,paid,void'],
            'method' => ['nullable', 'in:cash,card,transfer,other'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ];
    }

    public function filters(): array
    {
        $v = $this->validated();

        return [
            'q' => isset($v['q']) ? trim((string) $v['q']) : null,
            'status' => $v['status'] ?? null,
            'method' => $v['method'] ?? null,
            'from' => $v['from'] ?? null,
            'to' => $v['to'] ?? null,
        ];
    }
}
