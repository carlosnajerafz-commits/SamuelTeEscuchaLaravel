@extends('layouts.public')
@section('title', 'Acceso del personal')
@section('stage_class', 'login-stage')
@push('styles')
<style>
    .login-stage { min-height: 100vh; min-height: 100svh; display: flex; flex-direction: column; }
    .login-stage > .topbar { flex-shrink: 0; }
    .login-stage > main.wrap { flex: 1; width: 100%; display: flex; flex-direction: column; justify-content: center; padding: clamp(32px, 6vh, 72px) 16px; }
    .login-stage .login-card { width: 100%; max-width: 480px; margin: 0 auto; }
    .login-stage .notice { width: 100%; max-width: 480px; margin: 0 auto 24px; }
    .login-stage .notice ul { padding-left: 20px; margin: 0; }
</style>
@endpush
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
