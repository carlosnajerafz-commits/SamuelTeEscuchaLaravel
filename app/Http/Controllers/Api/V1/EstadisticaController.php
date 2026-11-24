<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Queja;
use App\Models\RegistroApoyo;
use App\Models\RegistroComedor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * Indicadores agregados del sistema.
 *
 * Solo devuelve conteos: ningún dato personal sale por aquí.
 * Pensado para alimentar tableros externos o el informe de la oficina.
 */
class EstadisticaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'datos' => [
                'generado_en' => now()->toIso8601String(),

                'citas' => [
                    'total' => Cita::count(),
                    'por_estatus' => $this->porEstatus(Cita::class),
                ],

                'quejas' => [
                    'total' => Queja::count(),
                    'por_estatus' => $this->porEstatus(Queja::class),
                    'por_tipo' => Queja::query()
                        ->selectRaw('tipo, COUNT(*) as total')
                        ->groupBy('tipo')
                        ->orderByDesc('total')
                        ->pluck('total', 'tipo'),
                ],

                'apoyos' => [
                    'total' => RegistroApoyo::count(),
                    'por_estatus' => $this->porEstatus(RegistroApoyo::class),
                    'por_apoyo' => RegistroApoyo::query()
                        ->selectRaw('apoyo, COUNT(*) as total')
                        ->groupBy('apoyo')
                        ->orderByDesc('total')
                        ->pluck('total', 'apoyo'),
                ],

                'comedor' => [
                    'registros' => RegistroComedor::count(),
                    'personas_totales' => (int) RegistroComedor::where('estatus', '!=', 'cancelado')
                        ->sum('numero_personas'),
                ],
            ],
        ]);
    }

    /**
     * Conteo agrupado por estatus para cualquier modelo que tenga esa columna.
     *
     * @param  class-string<Model>  $modelo
     */
    private function porEstatus(string $modelo): Collection
    {
        return $modelo::query()
            ->selectRaw('estatus, COUNT(*) as total')
            ->groupBy('estatus')
            ->orderByDesc('total')
            ->pluck('total', 'estatus');
    }
}
