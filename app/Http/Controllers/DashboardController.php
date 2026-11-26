<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\EventoComedor;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Resumen del panel: el primer estado configurado de cada módulo es el que
     * indica que la solicitud aún no ha sido atendida.
     */
    public function __invoke(): View
    {
        $summaries = collect(config('samuel.modules'))->map(fn (array $definition) => [
            'title' => $definition['title'],
            'total' => $definition['model']::count(),
            'pending' => $definition['model']::where('estatus', $definition['statuses'][0])->count(),
            'today' => $definition['model']::where('created_at', '>=', today())->count(),
        ]);
        $upcomingAppointments = Cita::where('fecha', '>=', today()->toDateString())->whereIn('estatus', ['solicitada', 'aceptada'])->orderBy('fecha')->orderBy('hora')->limit(8)->get();
        $upcomingEvents = EventoComedor::where('fecha', '>=', today()->toDateString())->where('activo', true)->withSum(['registros as inscritos' => fn ($query) => $query->where('estatus', '!=', 'cancelado')], 'numero_personas')->orderBy('fecha')->limit(5)->get();

        return view('dashboard', compact('summaries', 'upcomingAppointments', 'upcomingEvents'));
    }
}
