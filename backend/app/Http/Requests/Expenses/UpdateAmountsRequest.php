<?php

namespace App\Http\Requests\Expenses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAmountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_due' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'month' => ['nullable', 'date'],
            'q' => ['nullable', 'string'],
        ];
    }

    /** @return array{amount_due_cents:int,amount_paid_cents:int,month:?string,q:?string} */
    public function payload(): array
    {
        $v = $this->validated();

        return [
            'amount_due_cents' => (int) round(((float) $v['amount_due']) * 100),
            'amount_paid_cents' => (int) round(((float) ($v['amount_paid'] ?? 0)) * 100),
            'month' => isset($v['month']) ? (string) $v['month'] : null,
            'q' => isset($v['q']) ? trim((string) $v['q']) : null,
        ];
    }
}
