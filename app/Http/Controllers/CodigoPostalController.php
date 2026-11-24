<?php

namespace App\Http\Controllers;

use App\Http\Requests\CodigoPostalRequest;
use App\Models\CodigoPostal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CodigoPostalController extends Controller
{
    public function lookup(Request $request): JsonResponse
    {
        $data = $request->validate(['cp' => ['required', 'regex:/^[0-9]{5}$/']]);
        $rows = CodigoPostal::where('codigo_postal', $data['cp'])->orderBy('colonia')->get(['colonia', 'municipio']);

        return response()->json(['colonias' => $rows->pluck('colonia')->unique()->values(), 'municipio' => $rows->first()?->municipio, 'resultados' => $rows]);
    }

    public function index(Request $request): View
    {
        $data = $request->validate(['cp' => ['nullable', 'string', 'max:10']]);
        $codes = CodigoPostal::query()->when($data['cp'] ?? null, fn ($query, $cp) => $query->where('codigo_postal', $cp))->orderBy('codigo_postal')->orderBy('colonia')->paginate(30)->withQueryString();

        return view('codigos-postales.index', compact('codes'));
    }

    public function create(): View
    {
        return view('codigos-postales.form', ['code' => new CodigoPostal]);
    }

    public function store(CodigoPostalRequest $request): RedirectResponse
    {
        CodigoPostal::create($request->validated());

        return to_route('codigos-postales.index')->with('success', 'Código postal registrado.');
    }

    public function edit(int $codigos_postale): View
    {
        return view('codigos-postales.form', ['code' => CodigoPostal::findOrFail($codigos_postale)]);
    }

    public function update(CodigoPostalRequest $request, int $codigos_postale): RedirectResponse
    {
        CodigoPostal::findOrFail($codigos_postale)->update($request->validated());

        return to_route('codigos-postales.index')->with('success', 'Código postal actualizado.');
    }

    public function destroy(int $codigos_postale): RedirectResponse
    {
        CodigoPostal::findOrFail($codigos_postale)->delete();

        return to_route('codigos-postales.index')->with('success', 'Código postal eliminado.');
    }
}
