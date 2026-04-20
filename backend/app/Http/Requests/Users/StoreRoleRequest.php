<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '_form' => ['nullable', 'string'],

            // Preserve filters from the Users page.
            'q' => ['nullable', 'string'],
            'role_id' => ['nullable'],
            'role_q' => ['nullable', 'string'],

            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/i', 'unique:roles,name'],
        ];
    }

    /** @return array{name:string} */
    public function payload(): array
    {
        $v = $this->validated();

        return [
            'name' => strtolower(trim((string) $v['name'])),
        ];
    }
}
