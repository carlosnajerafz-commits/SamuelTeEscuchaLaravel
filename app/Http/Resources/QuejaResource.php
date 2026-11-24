<?php

namespace App\Http\Resources;

use App\Services\SeguimientoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuejaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'folio' => SeguimientoService::folio('quejas', $this->id),
            'id' => $this->id,
            'estatus' => $this->estatus,

            'ciudadano' => [
                'nombre_completo' => trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}"),
                'celular_1' => $this->celular_1,
                'correo' => $this->correo,
            ],

            'domicilio' => [
                'calle' => $this->calle,
                'no_exterior' => $this->no_exterior,
                'colonia' => $this->colonia,
                'municipio' => $this->municipio,
                'codigo_postal' => $this->codigo_postal,
            ],

            'reporte' => [
                'tipo' => $this->tipo,
                'descripcion' => $this->descripcion,
            ],

            'tiene_evidencia' => ! empty($this->evidencia_path),
            'registrado_en' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
