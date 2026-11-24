@extends('adminlte::page')
@section('title', 'Códigos postales')
@section('content_header')<h1>Códigos postales</h1>@endsection
@section('content')
@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
<form class="d-flex gap-2 mb-3" method="GET"><label for="cp" class="visually-hidden">Código postal</label><input class="form-control" id="cp" name="cp" placeholder="Código postal" value="{{ request('cp') }}"><button class="btn btn-primary">Buscar</button><a class="btn btn-secondary" href="{{ route('codigos-postales.index') }}">Limpiar</a></form>
<a class="btn btn-primary mb-3" href="{{ route('codigos-postales.create') }}">Agregar colonia</a>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>CP</th><th>Colonia</th><th>Municipio</th><th>Acciones</th></tr></thead><tbody>
@forelse($codes as $code)<tr><td>{{ $code->codigo_postal }}</td><td>{{ $code->colonia }}</td><td>{{ $code->municipio }}</td><td><a href="{{ route('codigos-postales.edit', $code) }}">Editar</a><form class="d-inline" method="POST" action="{{ route('codigos-postales.destroy', $code) }}" onsubmit="return confirm('¿Eliminar esta colonia del catálogo?')">@csrf @method('DELETE')<button class="btn btn-link text-danger">Eliminar</button></form></td></tr>
@empty<tr><td colspan="4">No hay resultados.</td></tr>@endforelse
</tbody></table></div>{{ $codes->links() }}
@endsection