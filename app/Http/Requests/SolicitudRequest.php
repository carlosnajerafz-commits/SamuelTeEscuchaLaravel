<?php

namespace App\Http\Requests;

use App\Models\CodigoPostal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return array_key_exists($this->route('module'), config('samuel.modules'));
    }

    public function rules(): array
    {
        $module = $this->route('module');
        $rules = [];
        foreach (['nombre' => 100, 'apellido_paterno' => 100, 'apellido_materno' => 100, 'calle' => 150, 'no_exterior' => 20, 'colonia' => 150, 'municipio' => 150] as $field => $length) {
            $rules[$field] = ['required', 'string', 'max:'.$length];
        }
        $rules += [
            'celular_1' => ['required', 'regex:/^[0-9]{10}$/'],
            'celular_2' => [in_array($module, ['citas', 'apoyos']) ? 'required' : 'nullable', 'regex:/^[0-9]{10}$/'],
            'correo' => [$module === 'comedor' ? 'nullable' : 'required', 'email', 'max:150'],
            'no_interior' => ['nullable', 'string', 'max:20'],
            'codigo_postal' => ['required', 'regex:/^[0-9]{5}$/'],
        ];
        if ($module !== 'citas') {
            $rules['seccion_electoral'] = ['nullable', 'regex:/^[0-9]{1,10}$/'];
        }

        return $rules + match ($module) {
            'quejas' => ['tipo' => ['required', 'string', 'max:150'], 'descripcion' => ['required', 'string', 'max:500'], 'evidencia' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'dimensions:min_width=1,min_height=1,max_width=12000,max_height=12000', 'max:5120']],
            'citas' => ['fecha' => ['required', 'date_format:Y-m-d', 'after:today'], 'hora' => ['required', Rule::in(config('samuel.slots'))], 'motivo' => ['nullable', 'string', 'max:2000']],
            'apoyos' => ['apoyo' => ['required', 'string', Rule::exists('apoyos', 'nombre')->where('activo', true)], 'observaciones' => ['nullable', 'string', 'max:500']],
            'comedor' => ['evento_id' => ['required', 'integer', 'exists:eventos_comedor,id'], 'numero_personas' => ['required', 'integer', 'between:1,20'], 'categoria_vulnerable' => ['required', Rule::in(config('samuel.categories'))], 'observaciones' => ['nullable', 'string', 'max:500']],
        };
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if (! CodigoPostal::where($this->only(['codigo_postal', 'colonia', 'municipio']))->exists()) {
                $validator->errors()->add('colonia', 'La colonia y el municipio deben corresponder al código postal.');
            }
            if ($this->route('module') === 'citas' && ! $this->date('fecha')->isTuesday()) {
                $validator->errors()->add('fecha', 'Selecciona un martes posterior a hoy.');
            }
        }];
    }
}
