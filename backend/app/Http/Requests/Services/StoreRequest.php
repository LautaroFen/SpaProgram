<?php

namespace App\Http\Requests\Services;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '_form' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'is_active' => ['nullable'],

            'name' => ['required', 'string', 'max:120'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active_new' => ['nullable', 'boolean'],
        ];
    }

    /** @return array{name:string,duration_minutes:int,price_cents:int,is_active:bool} */
    public function payload(): array
    {
        $v = $this->validated();

        $price = (float) $v['price'];
        $priceCents = (int) round($price * 100);

        return [
            'name' => trim((string) $v['name']),
            'duration_minutes' => (int) $v['duration_minutes'],
            'price_cents' => $priceCents,
            'is_active' => array_key_exists('is_active_new', $v) ? (bool) $v['is_active_new'] : true,
        ];
    }
}
