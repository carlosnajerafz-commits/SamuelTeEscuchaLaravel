<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['usuario' => ['required', 'string', 'max:50'], 'password' => ['required', 'string', 'max:1024']];
    }

    public function messages(): array
    {
        return ['usuario.required' => 'Captura tu usuario.', 'password.required' => 'Captura tu contraseña.'];
    }
}
