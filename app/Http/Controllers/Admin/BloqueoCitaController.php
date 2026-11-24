<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloqueoCita;
use App\Services\CitaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BloqueoCitaController extends Controller
{
    public function index(): View
    {
        $blocks = BloqueoCita::where('fecha', '>=', today()->toDateString())->orderBy('fecha')->paginate(30);

        return view('admin.bloqueos', compact('blocks'));
    }

    public function store(Request $request, CitaService $service): RedirectResponse
    {
        $data = $request->validate(['fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'], 'dia_completo' => ['required', 'boolean'], 'horas' => ['required_if:dia_completo,0', 'array'], 'horas.*' => [Rule::in(config('samuel.slots'))], 'motivo' => ['nullable', 'string', 'max:255']]);
        if (! $request->date('fecha')->isTuesday()) {
            throw ValidationException::withMessages(['fecha' => 'Selecciona un martes.']);
        }
        DB::transaction(function () use ($data, $request, $service) {
            $service->lockDay($data['fecha']);
            $base = ['fecha' => $data['fecha'], 'motivo' => $data['motivo'] ?? '', 'empleado_id' => $request->user()->id];
            if ($request->boolean('dia_completo')) {
                BloqueoCita::where('fecha', $data['fecha'])->delete();
                BloqueoCita::create([...$base, 'dia_completo' => true, 'hora' => null]);
            } else {
                foreach ($data['horas'] as $hour) {
                    BloqueoCita::firstOrCreate(['fecha' => $data['fecha'], 'hora' => $hour.':00'], [...$base, 'dia_completo' => false]);
                }
            }
        });

        return back()->with('success', 'Bloqueo registrado. Las citas existentes conservan su estado.');
    }

    public function destroy(int $id, CitaService $service): RedirectResponse
    {
        DB::transaction(function () use ($id, $service) {
            $block = BloqueoCita::findOrFail($id);
            $service->lockDay($block->fecha);
            $block->delete();
        });

        return back()->with('success', 'Bloqueo eliminado.');
    }
}
