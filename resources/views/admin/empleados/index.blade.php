@extends('adminlte::page')
@section('title', 'Empleados')
@section('content_header')<h1>Empleados</h1>@endsection
@section('content')
@include('admin.messages')
<a class="btn btn-primary mb-3" href="{{ route('admin.empleados.create') }}">Agregar empleado</a>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Nombre</th><th>Usuario</th><th>Acciones</th></tr></thead><tbody>
@forelse($employees as $employee)<tr><td>{{ $employee->nombre_completo }} @if(auth()->user()->is($employee))<span class="badge text-bg-info">Tú</span>@endif</td><td>{{ $employee->usuario }}</td><td><a href="{{ route('admin.empleados.edit', $employee) }}">Editar</a>@unless(auth()->user()->is($employee))<form class="d-inline" method="POST" action="{{ route('admin.empleados.destroy', $employee) }}" onsubmit="return confirm('¿Eliminar el acceso de este empleado?')">@csrf @method('DELETE')<button class="btn btn-link text-danger">Eliminar</button></form>@endunless</td></tr>
@empty<tr><td colspan="3">No hay empleados.</td></tr>@endforelse
</tbody></table></div>{{ $employees->links() }}
@endsection
