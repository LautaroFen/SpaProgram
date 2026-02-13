<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'has_debt' => ['nullable'],

            'first_name' => ['required', 'string', 'max:100', 'regex:'.self::LETTERS_ONLY],
            'last_name' => ['required', 'string', 'max:100', 'regex:'.self::LETTERS_ONLY],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\d+$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'dni' => ['nullable', 'string', 'max:32', 'regex:/^\d+$/', Rule::unique('clients', 'dni')],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.regex' => 'El nombre solo puede contener letras.',
            'last_name.regex' => 'El apellido solo puede contener letras.',
            'phone.regex' => 'El teléfono solo puede contener números.',
            'dni.unique' => 'Ya existe un cliente con ese DNI.',
            'dni.regex' => 'El DNI solo puede contener números.',
        ];
    }

    /** @return array{first_name:string,last_name:string,phone:string,email:?string,dni:?string} */
    public function payload(): array
    {
        $v = $this->validated();

        return [
            'first_name' => trim((string) $v['first_name']),
            'last_name' => trim((string) $v['last_name']),
            'phone' => trim((string) $v['phone']),
            'email' => isset($v['email']) ? trim((string) $v['email']) : null,
            'dni' => isset($v['dni']) ? trim((string) $v['dni']) : null,
        ];
    }
}
