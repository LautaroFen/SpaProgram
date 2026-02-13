<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateRequest extends FormRequest
{
    private const LETTERS_ONLY = '/^[\pL\pM]+(?:[ \-\"][\pL\pM]+)*$/u';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = (int) ($this->route('user')?->id ?? 0);

        return [
            'first_name' => ['required', 'string', 'max:100', 'regex:'.self::LETTERS_ONLY],
            'last_name' => ['required', 'string', 'max:100', 'regex:'.self::LETTERS_ONLY],
            'job_title' => ['nullable', 'string', 'max:120', 'regex:'.self::LETTERS_ONLY],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.regex' => 'El nombre solo puede contener letras.',
            'last_name.regex' => 'El apellido solo puede contener letras.',
            'job_title.regex' => 'El cargo solo puede contener letras.',
        ];
    }

    /** @return array{first_name:string,last_name:string,job_title:?string,role_id:int,email:string,password?:string,is_active:bool} */
    public function payload(): array
    {
        $v = $this->validated();

        $payload = [
            'first_name' => trim((string) $v['first_name']),
            'last_name' => trim((string) $v['last_name']),
            'job_title' => isset($v['job_title']) && $v['job_title'] !== null ? trim((string) $v['job_title']) : null,
            'role_id' => (int) $v['role_id'],
            'email' => trim((string) $v['email']),
            'is_active' => (bool) $v['is_active'],
        ];

        if (array_key_exists('password', $v) && $v['password'] !== null && trim((string) $v['password']) !== '') {
            $payload['password'] = (string) $v['password'];
        }

        return $payload;
    }
}
