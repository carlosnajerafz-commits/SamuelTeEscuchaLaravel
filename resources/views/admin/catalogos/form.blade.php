@extends('adminlte::page')
@section('content_header')<h1>{{ $record->exists ? 'Editar' : 'Agregar' }} {{ $catalog === 'comedor' ? 'evento' : 'apoyo' }}</h1>@endsection
@section('content')
@include('admin.messages')
<form method="POST" action="{{ $record->exists ? route('admin.catalogos.update', [$catalog, $record->id]) : route('admin.catalogos.store', $catalog) }}">@csrf @if($record->exists) @method('PUT') @endif
@foreach($fields as $field => $label)
<div class="mb-3"><label for="{{ $field }}">{{ $label }}</label><input class="form-control" id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'fecha' ? 'date' : (str_starts_with($field, 'hora_') ? 'time' : ($field === 'cupo_maximo' ? 'number' : 'text')) }}" value="{{ old($field, str_starts_with($field, 'hora_') ? substr($record->$field ?? '', 0, 5) : $record->$field) }}" @required(in_array($field, ['nombre','fecha','hora_inicio','hora_fin','lugar','cupo_maximo']))></div>
@endforeach
<label for="activo">Estado</label><select class="form-select mb-3" id="activo" name="activo"><option value="1" @selected(old('activo', $record->activo ?? true))>Activo</option><option value="0" @selected(!old('activo', $record->activo ?? true))>Inactivo</option></select><button class="btn btn-primary">Guardar</button>
</form>
@endsection