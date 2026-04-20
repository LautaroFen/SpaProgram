<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreRequest extends FormRequest
{
    private const LETTERS_ONLY = '/^[\pL\pM]+(?:[ \-\"][\pL\pM]+)*$/u';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '_form' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'role_id' => ['nullable'],

            'new_role_id' => ['required', 'integer', 'exists:roles,id'],
            'first_name' => ['required', 'string', 'max:100', 'regex:'.self::LETTERS_ONLY],
            'last_name' => ['required', 'string', 'max:100', 'regex:'.self::LETTERS_ONLY],
            'job_title' => ['nullable', 'string', 'max:120', 'regex:'.self::LETTERS_ONLY],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
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

    /** @return array{role_id:int,first_name:string,last_name:string,job_title:?string,email:string,password:string} */
    public function payload(): array
    {
        $v = $this->validated();

        return [
            'role_id' => (int) $v['new_role_id'],
            'first_name' => trim((string) $v['first_name']),
            'last_name' => trim((string) $v['last_name']),
            'job_title' => isset($v['job_title']) && $v['job_title'] !== null ? trim((string) $v['job_title']) : null,
            'email' => trim((string) $v['email']),
            'password' => (string) $v['password'],
        ];
    }
}
