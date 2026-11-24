@extends('adminlte::page')
@section('title', $definition['title'])
@section('content_header')<h1>{{ $definition['title'] }}</h1>@endsection
@section('content')
@include('admin.messages')
<div class="d-flex gap-3 flex-wrap mb-3">@foreach($statistics as $status => $count)<span class="badge text-bg-secondary">{{ $status }}: {{ $count }}</span>@endforeach</div>
@if($categories->isNotEmpty())<details class="mb-3"><summary>Personas por grupo de atención</summary><ul>@foreach($categories as $category => $count)<li>{{ $category ?: 'Sin categoría' }}: {{ $count }}</li>@endforeach</ul></details>@endif
<form class="d-flex gap-2 mb-3" method="GET"><input aria-label="Nombre o celular" class="form-control" name="q" value="{{ request('q') }}" placeholder="Nombre o celular"><select aria-label="Estado" class="form-select" name="estatus"><option value="">Todos los estados</option>@foreach($definition['statuses'] as $status)<option @selected(request('estatus') === $status)>{{ $status }}</option>@endforeach</select><button class="btn btn-primary">Filtrar</button></form>
<p><a href="{{ route('admin.solicitudes.export', ['module' => $module] + request()->only('q', 'estatus')) }}">Exportar CSV para Excel</a> · <a href="{{ route('solicitudes.create', $module) }}">Formulario público</a>@if(in_array($module, ['apoyos','comedor'])) · <a href="{{ route('admin.catalogos.index', $module) }}">Administrar {{ $module === 'comedor' ? 'eventos y menú' : 'apoyos disponibles' }}</a>@endif @if($module === 'citas') · <a href="{{ route('admin.bloqueos.index') }}">Bloqueos de calendario</a>@endif</p>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Folio</th><th>Solicitante</th><th>Celular</th><th>Estado</th><th>Detalle</th></tr></thead><tbody>
@forelse($records as $record)<tr><td>{{ $record->id }}</td><td>{{ $record->nombre }} {{ $record->apellido_paterno }} {{ $record->apellido_materno }}</td><td>{{ $record->celular_1 }}</td><td>{{ $record->estatus }}</td><td><a href="{{ route('admin.solicitudes.show', [$module, $record->id]) }}">Abrir solicitud</a></td></tr>@empty<tr><td colspan="5">No hay solicitudes.</td></tr>@endforelse
</tbody></table></div>{{ $records->links() }}
@endsection