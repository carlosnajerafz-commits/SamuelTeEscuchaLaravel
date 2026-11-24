@extends('adminlte::page')
@section('title', 'Solicitud '.$record->id)
@section('content_header')<h1>{{ $definition['title'] }} · Folio {{ $record->id }}</h1>@endsection
@section('content')
@include('admin.messages')
<dl class="row">@foreach($record->getAttributes() as $field => $value)@unless(in_array($field, ['evidencia_path', 'ine_path']))<dt class="col-sm-3">{{ ucfirst(str_replace('_', ' ', $field)) }}</dt><dd class="col-sm-9" style="white-space:pre-wrap">{{ $value }}</dd>@endunless @endforeach</dl>
@if($record->evidencia_path || $record->ine_path)<p><a href="{{ route('admin.solicitudes.evidence', [$module, $record->id]) }}">Descargar evidencia</a></p>@endif
<form method="POST" action="{{ route('admin.solicitudes.update', [$module, $record->id]) }}" class="d-flex gap-2 mb-3">@csrf @method('PATCH')<label for="estatus">Estado</label><select class="form-select" id="estatus" name="estatus">@foreach($definition['statuses'] as $status)<option @selected($record->estatus === $status)>{{ $status }}</option>@endforeach</select><button class="btn btn-primary">Actualizar</button></form>
<form method="POST" action="{{ route('admin.solicitudes.destroy', [$module, $record->id]) }}" onsubmit="return confirm('¿Eliminar definitivamente esta solicitud?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Eliminar solicitud</button></form><p class="mt-3"><a href="{{ route('admin.solicitudes.index', $module) }}">Volver al listado</a></p>
@endsection