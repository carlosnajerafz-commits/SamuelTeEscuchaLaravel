<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apoyo;
use App\Models\EventoComedor;
use App\Models\RegistroComedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    private function model(string $catalog): string
    {
        abort_unless(in_array($catalog, ['apoyos', 'comedor']), 404);

        return $catalog === 'apoyos' ? Apoyo::class : EventoComedor::class;
    }

    public function index(string $catalog): View
    {
        $records = $this->model($catalog)::orderByDesc('id')->paginate(25);

        return view('admin.catalogos.index', compact('catalog', 'records'));
    }

    public function form(string $catalog, ?int $id = null): View
    {
        $model = $this->model($catalog);
        $record = $id ? $model::findOrFail($id) : new $model;
        $fields = $catalog === 'apoyos' ? ['nombre' => 'Nombre', 'descripcion' => 'Descripción'] : ['fecha' => 'Fecha', 'hora_inicio' => 'Hora de inicio', 'hora_fin' => 'Hora de cierre', 'lugar' => 'Lugar', 'descripcion' => 'Descripción', 'cupo_maximo' => 'Cupo máximo (0 = sin límite)', 'tipo_comedor' => 'Tipo de comedor', 'grupo_dirigido' => 'Grupo dirigido', 'menu' => 'Menú'];

        return view('admin.catalogos.form', compact('catalog', 'record', 'fields'));
    }

    public function save(Request $request, string $catalog, ?int $id = null): RedirectResponse
    {
        $model = $this->model($catalog);
        $rules = $catalog === 'apoyos' ? ['nombre' => ['required', 'string', 'max:150'], 'descripcion' => ['nullable', 'string', 'max:5000']] : ['fecha' => ['required', 'date_format:Y-m-d'], 'hora_inicio' => ['required', 'date_format:H:i'], 'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'], 'lugar' => ['required', 'string', 'max:255'], 'descripcion' => ['nullable', 'string', 'max:5000'], 'cupo_maximo' => ['required', 'integer', 'min:0'], 'tipo_comedor' => ['nullable', 'string', 'max:100'], 'grupo_dirigido' => ['nullable', 'string', 'max:150'], 'menu' => ['nullable', 'string', 'max:5000']];
        $data = array_map(fn ($value) => $value ?? '', $request->validate($rules + ['activo' => ['required', 'boolean']]));
        DB::transaction(function () use ($model, $catalog, $id, $data) {
            $record = $id ? $model::whereKey($id)->lockForUpdate()->firstOrFail() : new $model;
            if ($catalog === 'comedor' && $id && $data['cupo_maximo'] > 0 && RegistroComedor::where('evento_id', $id)->where('estatus', '!=', 'cancelado')->sum('numero_personas') > $data['cupo_maximo']) {
                throw ValidationException::withMessages(['cupo_maximo' => 'El cupo no puede ser inferior al número de personas ya inscritas.']);
            }
            $record->fill($data)->save();
        });

        return to_route('admin.catalogos.index', $catalog)->with('success', 'Catálogo actualizado.');
    }
}
