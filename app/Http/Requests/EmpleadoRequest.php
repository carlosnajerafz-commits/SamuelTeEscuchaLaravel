<?php

namespace App\Http\Requests;

use App\Models\Empleado;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class EmpleadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['usuario' => trim((string) $this->input('usuario')), 'nombre_completo' => trim((string) $this->input('nombre_completo'))]);
    }

    /**
     * El inicio de sesión compara el usuario sin distinguir mayúsculas ni espacios,
     * así que la unicidad se valida con el mismo criterio.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $employee = $this->route('empleado');
        $isUsernameTaken = function (string $attribute, mixed $value, Closure $fail) use ($employee): void {
            $exists = Empleado::whereRaw('LOWER(TRIM(usuario)) = LOWER(?)', [$value])->when($employee, fn ($query) => $query->whereKeyNot($employee->id))->exists();
            if ($exists) {
                $fail('Ese usuario ya está registrado.');
            }
        };

        return [
            'usuario' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', $isUsernameTaken],
            'nombre_completo' => ['required', 'string', 'max:120'],
            'password' => [$employee ? 'nullable' : 'required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['usuario.regex' => 'El usuario solo puede contener letras, números, punto, guion y guion bajo.'];
    }
}
