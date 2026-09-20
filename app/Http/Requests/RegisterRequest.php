<?php

namespace App\Http\Requests;

use App\Enum\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        
        'name'                  => ['required', 'string', 'max:255'],
        'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password'              => ['required', 'string', 'min:8', 'confirmed'], // Exige enviar 'password_confirmation'
         
        // El rol es obligatorio y SOLO puede ser uno de los definidos en el Enum:
        'role'     => ['required', 'string', Rule::enum(UserRole::class)],
    
        ];
    }

    public function messages(): array
    {
        return [
        'email.required'     => 'El correo electrónico es obligatorio.',
        'email.email'        => 'Debe ingresar un correo válido.',
        'email.unique'       => 'Este correo electrónico ya se encuentra registrado.',
        'password.required'  => 'La contraseña es obligatoria.',
        'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
        'role.required'      => 'El rol es obligatorio.',
        'role.enum'          => 'El rol seleccionado no es válido. Debe ser admin, operario o veterinario.',
        ];
    }
}
