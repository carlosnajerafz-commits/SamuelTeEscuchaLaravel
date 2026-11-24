<?php

namespace App\Http\Resources;

use App\Services\SeguimientoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistroComedorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'folio' => SeguimientoService::folio('comedor', $this->id),
            'id' => $this->id,
            'evento_id' => $this->evento_id,
            'estatus' => $this->estatus,
            'ciudadano' => [
                'nombre_completo' => trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}"),
                'celular_1' => $this->celular_1,
                'correo' => $this->correo,
                'municipio' => $this->municipio,
                'colonia' => $this->colonia,
            ],
            'numero_personas' => (int) $this->numero_personas,
            'categoria_vulnerable' => $this->categoria_vulnerable,
            'observaciones' => $this->observaciones,
            'registrado_en' => $this->created_at?->toIso8601String(),
        ];
    }
}
