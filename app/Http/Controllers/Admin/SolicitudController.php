<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EstatusSolicitudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SolicitudController extends Controller
{
    private function definition(string $module): array
    {
        $definition = config('samuel.modules.'.$module);
        abort_unless(is_array($definition), 404);

        return $definition;
    }

    private function query(Request $request, array $definition): Builder
    {
        $filters = $request->validate(['estatus' => ['nullable', Rule::in($definition['statuses'])], 'q' => ['nullable', 'string', 'max:150']]);

        return $definition['model']::query()->when($filters['estatus'] ?? null, fn ($query, $status) => $query->where('estatus', $status))->when($filters['q'] ?? null, fn ($query, $term) => $query->where(fn ($query) => $query->where('nombre', 'like', '%'.$term.'%')->orWhere('apellido_paterno', 'like', '%'.$term.'%')->orWhere('celular_1', $term)));
    }

    public function index(Request $request, string $module): View
    {
        $definition = $this->definition($module);
        $records = $this->query($request, $definition)->orderByDesc('id')->paginate(25)->withQueryString();
        $statistics = $definition['model']::query()->select('estatus')->selectRaw('COUNT(*) as total')->groupBy('estatus')->pluck('total', 'estatus');
        $categories = $module === 'comedor' ? $definition['model']::query()->select('categoria_vulnerable')->selectRaw('SUM(numero_personas) as total')->where('estatus', '!=', 'cancelado')->groupBy('categoria_vulnerable')->pluck('total', 'categoria_vulnerable') : collect();

        return view('admin.solicitudes.index', compact('module', 'definition', 'records', 'statistics', 'categories'));
    }

    public function show(string $module, int $id): View
    {
        $definition = $this->definition($module);
        $record = $definition['model']::findOrFail($id);

        return view('admin.solicitudes.show', compact('module', 'definition', 'record'));
    }

    public function update(Request $request, string $module, int $id, EstatusSolicitudService $estatus): RedirectResponse
    {
        $definition = $this->definition($module);
        $data = $request->validate(['estatus' => ['required', Rule::in($definition['statuses'])]]);
        $estatus->update($module, $definition['model']::findOrFail($id), $data['estatus']);

        return back()->with('success', 'Estado actualizado.');
    }

    public function destroy(string $module, int $id): RedirectResponse
    {
        $definition = $this->definition($module);
        $definition['model']::findOrFail($id)->delete();

        return to_route('admin.solicitudes.index', $module)->with('success', 'Registro eliminado.');
    }

    public function evidence(string $module, int $id): BinaryFileResponse
    {
        $definition = $this->definition($module);
        $record = $definition['model']::findOrFail($id);
        $path = $record->evidencia_path ?? $record->ine_path;
        abort_unless(is_string($path), 404);
        if (preg_match('~^evidencias/[A-Za-z0-9_-]+\.(jpg|jpeg|png|webp)$~i', $path)) {
            $absolute = Storage::disk('local')->path($path);
        } elseif (preg_match('~^uploads/[A-Za-z0-9_-]+\.(jpg|jpeg|png|webp)$~i', $path) && config('samuel.legacy_uploads')) {
            $absolute = rtrim(config('samuel.legacy_uploads'), '/\\').DIRECTORY_SEPARATOR.basename($path);
        } else {
            abort(404);
        }
        abort_unless(is_file($absolute), 404);

        return response()->download($absolute, basename($path), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function export(Request $request, string $module): StreamedResponse
    {
        $definition = $this->definition($module);
        $query = $this->query($request, $definition)->orderBy('id');

        return response()->streamDownload(function () use ($query) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            $header = false;
            foreach ($query->lazy(200) as $record) {
                $row = $record->getAttributes();
                unset($row['evidencia_path'], $row['ine_path']);
                if (! $header) {
                    fputcsv($file, array_keys($row), ',', '"', '');
                    $header = true;
                }
                fputcsv($file, array_map(fn ($value) => preg_match('/^[\s]*[=+@\-]/u', (string) $value) ? "'".$value : $value, array_values($row)), ',', '"', '');
            }
            fclose($file);
        }, $module.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }
}
