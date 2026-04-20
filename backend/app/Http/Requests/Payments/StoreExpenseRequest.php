<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:80'],
            'payee' => ['required', 'string', 'max:160'],
            'amount_due' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'performed_at' => ['required', 'date'],
        ];
    }

    /** @return array{category:string,payee:string,amount_due_cents:int,amount_paid_cents:int,performed_at:string} */
    public function payload(): array
    {
        $v = $this->validated();

        return [
            'category' => trim((string) $v['category']),
            'payee' => trim((string) $v['payee']),
            'amount_due_cents' => (int) round(((float) $v['amount_due']) * 100),
            'amount_paid_cents' => (int) round(((float) ($v['amount_paid'] ?? 0)) * 100),
            'performed_at' => (string) $v['performed_at'],
        ];
    }
}
