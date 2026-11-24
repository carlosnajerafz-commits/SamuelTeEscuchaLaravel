<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Folios públicos y consulta de seguimiento ciudadano.
 *
 * Los folios son correlativos (CITA-000014 es la cita 14), así que la consulta
 * exige también el celular registrado y responde igual cuando el folio no existe
 * y cuando el celular no coincide, para no revelar qué folios están en uso.
 */
class SeguimientoService
{
    public const TELEFONO = '55 2172 4723';

    /** @var array<string, string> Módulo del sistema => prefijo de folio. */
    private const PREFIJOS = [
        'citas' => 'CITA',
        'quejas' => 'QUEJA',
        'apoyos' => 'APOYO',
        'comedor' => 'COMEDOR',
    ];

    /** @var array<string, string> Prefijo de folio => tipo legible. */
    private const TIPOS = [
        'CITA' => 'Cita ciudadana',
        'QUEJA' => 'Queja o reporte',
        'APOYO' => 'Solicitud de apoyo',
        'COMEDOR' => 'Comedor solidario',
    ];

    /** @var array<string, array<string, string>> */
    private const DESCRIPCIONES = [
        'CITA' => [
            'solicitada' => 'Tu solicitud está en revisión. El personal te contactará para confirmar la fecha.',
            'aceptada' => 'Tu cita quedó confirmada. Te esperamos en la fecha y hora acordadas.',
            'realizada' => 'La cita ya se llevó a cabo.',
            'cancelada' => 'La cita fue cancelada.',
            'rechazada' => 'La solicitud no pudo aceptarse. Llámanos al '.self::TELEFONO.' para conocer el motivo.',
            'vencida' => 'La fecha ya pasó y no se registró asistencia. Puedes agendar una nueva cita.',
        ],
        'QUEJA' => [
            'pendiente' => 'Tu reporte fue recibido y está en espera de revisión.',
            'atendida' => 'Tu reporte ya fue canalizado a la dependencia correspondiente.',
            'completada' => 'El reporte fue resuelto.',
            'cerrada' => 'El reporte fue cerrado.',
        ],
        'APOYO' => [
            'pendiente' => 'Tu solicitud fue recibida y está en revisión.',
            'en proceso' => 'Tu solicitud se está gestionando en este momento.',
            'entregado' => 'El apoyo fue entregado.',
            'cancelado' => 'La solicitud fue cancelada.',
        ],
        'COMEDOR' => [
            'pendiente' => 'Tu registro quedó hecho. Preséntate el día del evento.',
            'confirmado' => 'Tu lugar está confirmado.',
            'asistio' => 'Se registró tu asistencia. Gracias por acompañarnos.',
            'cancelado' => 'El registro fue cancelado.',
        ],
    ];

    /**
     * Folio público de un registro, por ejemplo CITA-000014.
     */
    public static function folio(string $module, int $id): string
    {
        return self::PREFIJOS[$module].'-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Busca un folio dentro de un texto libre ("mi folio es cita 14").
     *
     * @return array{prefijo: string, id: int, folio: string}|null
     */
    public function extraerFolio(string $texto): ?array
    {
        if (! preg_match('/\b(CITA|QUEJA|APOYO|COMEDOR)\s*-?\s*(\d{1,6})\b/i', $texto, $coincidencia)) {
            return null;
        }

        $prefijo = strtoupper($coincidencia[1]);
        $id = (int) $coincidencia[2];

        if ($id < 1) {
            return null;
        }

        return ['prefijo' => $prefijo, 'id' => $id, 'folio' => $prefijo.'-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT)];
    }

    /**
     * Estatus de un folio, solo si el celular coincide con alguno de los registrados.
     * Nunca devuelve datos personales.
     *
     * @return array{folio: string, tipo: string, estatus: string, descripcion: string, registrado_en: ?string}|null
     */
    public function consultar(string $folio, string $celular): ?array
    {
        $partes = $this->extraerFolio($folio);

        if (! $partes || ! preg_match('/^\d{10}$/', $celular)) {
            return null;
        }

        $module = array_search($partes['prefijo'], self::PREFIJOS, true);

        /** @var class-string<Model> $modelo */
        $modelo = config("samuel.modules.{$module}.model");

        $registro = $modelo::query()
            ->whereKey($partes['id'])
            ->where(fn ($query) => $query->where('celular_1', $celular)->orWhere('celular_2', $celular))
            ->first();

        if (! $registro) {
            return null;
        }

        $estatus = (string) $registro->estatus;

        return [
            'folio' => $partes['folio'],
            'tipo' => self::TIPOS[$partes['prefijo']],
            'estatus' => $estatus,
            'descripcion' => self::DESCRIPCIONES[$partes['prefijo']][$estatus] ?? 'Para conocer el detalle, llámanos al '.self::TELEFONO.'.',
            'registrado_en' => $registro->created_at?->toDateString(),
        ];
    }
}
