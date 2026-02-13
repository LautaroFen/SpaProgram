<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = (int) ($this->route('role')?->id ?? 0);

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/i',
                Rule::unique('roles', 'name')->ignore($roleId),
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array{name:string,is_active:bool} */
    public function payload(): array
    {
        $v = $this->validated();

        return [
            'name' => strtolower(trim((string) $v['name'])),
            'is_active' => (bool) $v['is_active'],
        ];
    }
}
