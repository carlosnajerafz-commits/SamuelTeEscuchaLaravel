<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inicio') | Samuel Te Escucha</title>
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <style>main.wrap{padding:32px 16px}form label{display:block;margin-top:16px}form input,form select,form textarea{display:block;width:100%;padding:10px;border:1px solid #aaa;border-radius:8px}form button{margin-top:20px}.login-card{max-width:480px;margin:30px auto;padding:28px;background:white;border-radius:16px}.notice{padding:16px;margin:16px 0;background:#fff3cd;color:#503b00}.success{background:#d1e7dd;color:#0f5132}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:20px}.card{background:white;padding:24px;border-radius:16px}.topbar a{color:inherit}.nav-links{display:flex;gap:18px;flex-wrap:wrap}.folio-card{margin:16px 0 28px;padding:24px;text-align:center;background:var(--card,#fff);border:2px solid var(--dorado,#C8A96A);border-radius:var(--radius,18px);box-shadow:var(--shadow)}.folio-card__titulo{margin:0 0 12px;font-weight:700;font-size:1.15rem;color:#0f5132}.folio-card__etiqueta{margin:0;color:var(--muted,#6b7280)}.folio-card__folio{margin:6px 0 14px;font:700 clamp(1.8rem,6vw,2.6rem)/1.1 ui-monospace,Consolas,monospace;letter-spacing:.06em;color:var(--vino,#7a1a3a);user-select:all}.folio-card__ayuda{margin:0 auto;max-width:520px;color:var(--text,#1f2937);font-size:.95rem}</style>
</head>
<body>
<header class="topbar"><div class="wrap topbar__inner"><a href="{{ route('home') }}" class="brand"><img src="{{ asset('assets/img/logo.png') }}" alt="" width="60"><strong>Samuel Te Escucha</strong></a><nav class="nav-links"><a href="{{ route('home') }}">Inicio</a>@foreach(config('samuel.modules') as $key => $definition)<a href="{{ route('solicitudes.create', $key) }}">{{ $definition['title'] }}</a>@endforeach<a href="{{ route('login') }}">Personal</a></nav></div></header>
<main class="wrap">
    @if ($errors->any())<div role="alert" class="notice"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if (session('folio'))
        <section role="status" class="folio-card">
            <p class="folio-card__titulo">¡Solicitud registrada!</p>
            <p class="folio-card__etiqueta">Tu folio de seguimiento es</p>
            <p class="folio-card__folio">{{ session('folio') }}</p>
            <p class="folio-card__ayuda">Guárdalo junto con el celular que registraste: con ambos puedes consultar el avance en el asistente <strong>(botón de abajo a la derecha)</strong> o llamando al 55 2172 4723.</p>
        </section>
    @elseif (session('success'))
        <div role="status" class="notice success">{{ session('success') }}</div>
    @endif
    @yield('content')
</main>
@stack('scripts')
<x-chatbot />
</body></html>