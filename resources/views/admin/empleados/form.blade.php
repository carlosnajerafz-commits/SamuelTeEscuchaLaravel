@extends('adminlte::page')
@section('title', 'Empleado')
@section('content_header')<h1>{{ $employee->exists ? 'Editar empleado' : 'Agregar empleado' }}</h1>@endsection
@section('content')
@include('admin.messages')
<form method="POST" action="{{ $employee->exists ? route('admin.empleados.update', $employee) : route('admin.empleados.store') }}" autocomplete="off">
@csrf @if($employee->exists) @method('PUT') @endif
<div class="mb-3"><label for="nombre_completo">Nombre completo</label><input class="form-control" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo', $employee->nombre_completo) }}" maxlength="120" required></div>
<div class="mb-3"><label for="usuario">Usuario</label><input class="form-control" id="usuario" name="usuario" value="{{ old('usuario', $employee->usuario) }}" maxlength="50" pattern="[A-Za-z0-9._\-]+" required></div>
<div class="mb-3"><label for="password">Contraseña @if($employee->exists)<span class="text-body-secondary">(déjala vacía para conservar la actual)</span>@endif</label><input class="form-control" id="password" type="password" name="password" minlength="8" autocomplete="new-password" @required(! $employee->exists)></div>
<div class="mb-3"><label for="password_confirmation">Confirmar contraseña</label><input class="form-control" id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" @required(! $employee->exists)></div>
<button class="btn btn-primary">Guardar</button> <a class="btn btn-secondary" href="{{ route('admin.empleados.index') }}">Cancelar</a>
</form>
@endsection
