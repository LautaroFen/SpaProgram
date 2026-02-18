<?php

namespace App\Http\Requests\Appointments;

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
            'week' => ['nullable', 'date'],
            '_form' => ['nullable', 'string'],
            '_edit_id' => ['nullable'],

            'service_id' => ['required', 'integer', 'exists:services,id'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],

            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],

            'deposit' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /** @return array{week:?string,service_id:int,client_id:int,user_id:int,start_date:string,start_time:string,deposit_cents:int} */
    public function payload(): array
    {
        $v = $this->validated();

        return [
            'week' => $v['week'] ?? null,
            'service_id' => (int) $v['service_id'],
            'client_id' => (int) $v['client_id'],
            'user_id' => (int) $v['user_id'],
            'start_date' => (string) $v['start_date'],
            'start_time' => (string) $v['start_time'],
            'deposit_cents' => (int) round(((float) ($v['deposit'] ?? 0)) * 100),
        ];
    }
}
