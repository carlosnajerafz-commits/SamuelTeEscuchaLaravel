@extends('adminlte::page')
@section('title', 'Panel de empleados')
@section('content_header')<h1>Samuel Te Escucha</h1>@endsection
@section('content')
<p>Bienvenido, {{ auth()->user()->nombre_completo }}.</p>
<div class="row">@foreach(config('samuel.modules') as $key => $module)<div class="col-md-6 col-xl-3"><div class="card mb-3"><div class="card-body"><h2 class="h5">{{ $module['title'] }}</h2><a href="{{ route('admin.solicitudes.index', $key) }}">Administrar solicitudes</a></div></div></div>@endforeach</div>
<a class="btn btn-primary" href="{{ route('codigos-postales.index') }}">Códigos postales</a> <a class="btn btn-secondary" href="{{ route('admin.bloqueos.index') }}">Bloqueos de citas</a>
@endsection