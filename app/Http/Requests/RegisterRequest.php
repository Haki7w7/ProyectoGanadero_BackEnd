<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
            'role'     => ['required', 'string', Rule::in(['admin', 'veterinario', 'operario'])],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'         => 'El correo electrónico es obligatorio.',
            'email.email'            => 'Debe ingresar un correo válido.',
            'email.unique'           => 'Este correo electrónico ya se encuentra registrado.',
            'password.required'      => 'La contraseña es obligatoria.',
            'password.min'           => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'     => 'Las contraseñas no coinciden.',
            'password.letters'       => 'La contraseña debe contener al menos una letra.',
            'password.mixed'         => 'La contraseña debe contener mayúsculas y minúsculas.',
            'password.numbers'       => 'La contraseña debe contener al menos un número.',
            'password.symbols'       => 'La contraseña debe contener al menos un símbolo.',
            'password.uncompromised' => 'La contraseña apareció en una filtración de datos. Elija otra.',
            'role.required'          => 'El rol es obligatorio.',
            'role.in'                => 'El rol seleccionado no es válido. Debe ser admin, veterinario u operario.',
      ];
    }
}
