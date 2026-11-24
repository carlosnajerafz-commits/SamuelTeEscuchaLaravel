@extends('layouts.public')
@section('title', 'Acceso del personal')
@section('content')
<section class="login-card">
    <h1>Iniciar sesión</h1>
    <p>Acceso exclusivo para personal autorizado.</p>
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label for="usuario">Usuario</label>
        <input id="usuario" name="usuario" value="{{ old('usuario') }}" required maxlength="50" autocomplete="username" autofocus>
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        <button class="btn" type="submit">Entrar</button>
    </form>
</section>
@endsection