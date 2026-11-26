@extends('adminlte::page')
@section('title', $catalog === 'comedor' ? 'Eventos de comedor' : 'Catálogo de apoyos')
@section('content_header')<h1>{{ $catalog === 'comedor' ? 'Eventos de comedor' : 'Catálogo de apoyos' }}</h1>@endsection
@section('content')
@include('admin.messages')
<a class="btn btn-primary mb-3" href="{{ route('admin.catalogos.create', $catalog) }}">Agregar</a>
<div class="table-responsive"><table class="table table-striped"><thead><tr>
@if($catalog === 'comedor')<th>Fecha</th><th>Horario</th><th>Lugar</th><th>Inscritos / cupo</th>@else<th>Nombre</th><th>Descripción</th>@endif
<th>Estado</th><th>Acciones</th></tr></thead><tbody>
@forelse($records as $record)<tr>
@if($catalog === 'comedor')<td>{{ $record->fecha }}</td><td>{{ substr($record->hora_inicio, 0, 5) }} – {{ substr($record->hora_fin, 0, 5) }}</td><td>{{ $record->lugar }}</td><td>{{ (int) $record->inscritos }} / {{ $record->cupo_maximo > 0 ? $record->cupo_maximo : 'sin límite' }}</td>@else<td>{{ $record->nombre }}</td><td>{{ \Illuminate\Support\Str::limit($record->descripcion, 80) }}</td>@endif
<td><span class="badge text-bg-{{ $record->activo ? 'success' : 'secondary' }}">{{ $record->activo ? 'Activo' : 'Inactivo' }}</span></td>
<td><a href="{{ route('admin.catalogos.edit', [$catalog, $record->id]) }}">Editar</a><form class="d-inline" method="POST" action="{{ route('admin.catalogos.destroy', [$catalog, $record->id]) }}" onsubmit="return confirm('¿Eliminar este registro del catálogo?')">@csrf @method('DELETE')<button class="btn btn-link text-danger">Eliminar</button></form></td>
</tr>@empty<tr><td colspan="6">No hay registros.</td></tr>@endforelse
</tbody></table></div>{{ $records->links() }}
@endsection
