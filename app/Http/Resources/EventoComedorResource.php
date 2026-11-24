<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Evento público del comedor solidario, con su cupo disponible.
 *
 * Requiere que la consulta traiga el acumulado `personas_registradas`
 * (ver ComedorController::index).
 */
class EventoComedorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cupoMaximo = (int) $this->cupo_maximo;
        $ocupado = (int) ($this->personas_registradas ?? 0);

        return [
            'id' => $this->id,
            'fecha' => $this->fecha,
            'hora_inicio' => substr((string) $this->hora_inicio, 0, 5),
            'hora_fin' => substr((string) $this->hora_fin, 0, 5),
            'lugar' => $this->lugar,
            'descripcion' => $this->descripcion,
            'tipo_comedor' => $this->tipo_comedor,
            'grupo_dirigido' => $this->grupo_dirigido,

            'cupo' => [
                'maximo' => $cupoMaximo,
                'ocupado' => $ocupado,
                // cupo_maximo = 0 significa sin límite declarado
                'disponible' => $cupoMaximo > 0 ? max(0, $cupoMaximo - $ocupado) : null,
                'ilimitado' => $cupoMaximo === 0,
            ],
        ];
    }
}
