<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '_form' => ['nullable', 'string'],
            '_edit_id' => ['nullable', 'integer'],

            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /** @return array{amount_cents:int} */
    public function payload(): array
    {
        $v = $this->validated();

        return [
            'amount_cents' => (int) round(((float) $v['amount']) * 100),
        ];
    }
}
