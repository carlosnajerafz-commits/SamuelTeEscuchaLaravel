<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmpleadoRequest;
use App\Models\Empleado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    public function index(): View
    {
        $employees = Empleado::orderBy('nombre_completo')->paginate(30);

        return view('admin.empleados.index', compact('employees'));
    }

    public function create(): View
    {
        return view('admin.empleados.form', ['employee' => new Empleado]);
    }

    public function store(EmpleadoRequest $request): RedirectResponse
    {
        Empleado::create([...$request->safe()->only('usuario', 'nombre_completo'), 'password_hash' => Hash::make($request->validated('password'))]);

        return to_route('admin.empleados.index')->with('success', 'Empleado registrado.');
    }

    public function edit(Empleado $empleado): View
    {
        return view('admin.empleados.form', ['employee' => $empleado]);
    }

    /**
     * Cambiar la contraseña revoca los tokens de la API emitidos con la anterior.
     */
    public function update(EmpleadoRequest $request, Empleado $empleado): RedirectResponse
    {
        $empleado->fill($request->safe()->only('usuario', 'nombre_completo'));
        if ($request->filled('password')) {
            $empleado->password_hash = Hash::make($request->validated('password'));
            $empleado->tokens()->delete();
        }
        $empleado->save();

        return to_route('admin.empleados.index')->with('success', 'Empleado actualizado.');
    }

    public function destroy(Request $request, Empleado $empleado): RedirectResponse
    {
        if ($request->user()->is($empleado)) {
            throw ValidationException::withMessages(['empleado' => 'No puedes eliminar tu propia cuenta.']);
        }
        $empleado->tokens()->delete();
        $empleado->delete();

        return to_route('admin.empleados.index')->with('success', 'Empleado eliminado.');
    }
}
