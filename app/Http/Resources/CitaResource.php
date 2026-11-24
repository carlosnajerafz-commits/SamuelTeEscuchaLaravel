<?php

namespace App\Http\Resources;

use App\Services\SeguimientoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Forma en que una cita sale por la API.
 *
 * Solo se usa en endpoints autenticados. El documento de identificación
 * (ine_path) nunca se expone como ruta pública: se indica si existe o no.
 */
class CitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'folio' => SeguimientoService::folio('citas', $this->id),
            'id' => $this->id,
            'estatus' => $this->estatus,

            'ciudadano' => [
                'nombre_completo' => trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}"),
                'celular_1' => $this->celular_1,
                'celular_2' => $this->celular_2,
                'correo' => $this->correo,
            ],

            'domicilio' => [
                'calle' => $this->calle,
                'no_exterior' => $this->no_exterior,
                'no_interior' => $this->no_interior,
                'colonia' => $this->colonia,
                'municipio' => $this->municipio,
                'codigo_postal' => $this->codigo_postal,
            ],

            'cita' => [
                'fecha' => $this->fecha,
                'hora' => substr((string) $this->hora, 0, 5),
                'motivo' => $this->motivo,
            ],

            'tiene_identificacion' => ! empty($this->ine_path),
            'registrado_en' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
