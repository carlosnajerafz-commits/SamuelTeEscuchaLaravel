@extends('adminlte::page')
@section('title', 'Colonia')
@section('content_header')<h1>{{ $code->exists ? 'Editar colonia' : 'Agregar colonia' }}</h1>@endsection
@section('content')
@if($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $code->exists ? route('codigos-postales.update', $code) : route('codigos-postales.store') }}">
@csrf @if($code->exists) @method('PUT') @endif
@foreach(['codigo_postal' => 'Código postal', 'colonia' => 'Colonia', 'municipio' => 'Municipio'] as $field => $label)
<div class="mb-3"><label for="{{ $field }}">{{ $label }}</label><input class="form-control" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $code->$field) }}" maxlength="{{ $field === 'codigo_postal' ? 5 : 150 }}" required></div>
@endforeach
<button class="btn btn-primary">Guardar</button><a class="btn btn-secondary" href="{{ route('codigos-postales.index') }}">Cancelar</a>
</form>
@endsection