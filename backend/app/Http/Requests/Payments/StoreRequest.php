<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'status' => ['nullable'],
            'method' => ['nullable'],
            'from' => ['nullable'],
            'to' => ['nullable'],

            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],

            'client_first_name' => ['required_without:client_id', 'nullable', 'string', 'max:100'],
            'client_last_name' => ['required_without:client_id', 'nullable', 'string', 'max:100'],
            'client_phone' => ['required_without:client_id', 'nullable', 'string', 'max:30'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_dni' => ['nullable', 'string', 'max:32'],

            'amount' => ['required', 'numeric', 'min:0.01'],
            'method_new' => ['required', 'in:cash,card,transfer,other'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $clientId = $this->input('client_id');
            $appointmentId = $this->input('appointment_id');

            if (! empty($appointmentId) && empty($clientId)) {
                $validator->errors()->add('client_id', 'Seleccioná un cliente para asociar un turno.');
            }
        });
    }

    /** @return array */
    public function payload(): array
    {
        $v = $this->validated();

        return [
            'client_id' => $v['client_id'] ?? null,
            'appointment_id' => $v['appointment_id'] ?? null,

            'client_first_name' => isset($v['client_first_name']) ? trim((string) $v['client_first_name']) : null,
            'client_last_name' => isset($v['client_last_name']) ? trim((string) $v['client_last_name']) : null,
            'client_phone' => isset($v['client_phone']) ? trim((string) $v['client_phone']) : null,
            'client_email' => isset($v['client_email']) ? trim((string) $v['client_email']) : null,
            'client_dni' => isset($v['client_dni']) ? trim((string) $v['client_dni']) : null,

            'amount_cents' => (int) round(((float) $v['amount']) * 100),
            'method' => (string) $v['method_new'],
            'reference' => isset($v['reference']) ? trim((string) $v['reference']) : null,
            'notes' => isset($v['notes']) ? trim((string) $v['notes']) : null,
        ];
    }
}
