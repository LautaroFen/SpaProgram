<?php

namespace App\Http\Requests\Appointments;

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
            'week' => ['nullable', 'date'],

            'service_id' => ['required', 'integer', 'exists:services,id'],

            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],

            'client_id' => ['nullable', 'integer', 'exists:clients,id'],

            'client_first_name' => ['required_without:client_id', 'nullable', 'string', 'max:100'],
            'client_last_name' => ['required_without:client_id', 'nullable', 'string', 'max:100'],
            'client_phone' => ['required_without:client_id', 'nullable', 'string', 'max:30'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_dni' => ['nullable', 'string', 'max:32'],

            'deposit' => ['nullable', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array{week:?string,service_id:int,start_date:string,start_time:string,client_id:?int,client_first_name:?string,client_last_name:?string,client_phone:?string,client_email:?string,client_dni:?string,deposit_cents:int,notes:?string} */
    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'week' => $validated['week'] ?? null,
            'service_id' => (int) $validated['service_id'],
            'start_date' => (string) $validated['start_date'],
            'start_time' => (string) $validated['start_time'],
            'client_id' => $validated['client_id'] ?? null,
            'client_first_name' => isset($validated['client_first_name']) ? trim((string) $validated['client_first_name']) : null,
            'client_last_name' => isset($validated['client_last_name']) ? trim((string) $validated['client_last_name']) : null,
            'client_phone' => isset($validated['client_phone']) ? trim((string) $validated['client_phone']) : null,
            'client_email' => isset($validated['client_email']) ? trim((string) $validated['client_email']) : null,
            'client_dni' => isset($validated['client_dni']) ? trim((string) $validated['client_dni']) : null,
            'deposit_cents' => (int) round(((float) ($validated['deposit'] ?? 0)) * 100),
            'notes' => isset($validated['notes']) ? trim((string) $validated['notes']) : null,
        ];
    }
}
