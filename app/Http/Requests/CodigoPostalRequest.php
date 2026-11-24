<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CodigoPostalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['codigo_postal' => ['required', 'regex:/^[0-9]{5}$/'], 'colonia' => ['required', 'string', 'max:150'], 'municipio' => ['required', 'string', 'max:150']];
    }
}
