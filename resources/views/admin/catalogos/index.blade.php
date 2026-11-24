@extends('adminlte::page')
@section('content_header')<h1>{{ $catalog === 'comedor' ? 'Eventos de comedor' : 'Catálogo de apoyos' }}</h1>@endsection
@section('content')
@include('admin.messages')
<a class="btn btn-primary mb-3" href="{{ route('admin.catalogos.create', $catalog) }}">Agregar</a>
<table class="table"><thead><tr><th>Nombre / Fecha</th><th>Estado</th><th>Acción</th></tr></thead><tbody>@foreach($records as $record)<tr><td>{{ $record->nombre ?? $record->fecha }} {{ $record->lugar }}</td><td>{{ $record->activo ? 'Activo' : 'Inactivo' }}</td><td><a href="{{ route('admin.catalogos.edit', [$catalog, $record->id]) }}">Editar</a></td></tr>@endforeach</tbody></table>{{ $records->links() }}
@endsection