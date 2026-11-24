<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enviar Queja | Samuel Te Escucha</title>
  <style>
    :root {
      --q-guinda: rgb(122, 23, 55);
      --q-bg: #f3f4f6;
      --q-text: #1f2937;
      --q-border: #e5e7eb;
      --q-shadow: 0 14px 38px rgba(17,24,39,.08);
    }
    body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: var(--q-bg); color: var(--q-text); }
    .q-wrap { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

    .q-topbar { background: #fff; border-bottom: 1px solid var(--q-border); position: sticky; top: 0; z-index: 100; padding: 12px 0; }
    .q-topbar-inner { display: flex; align-items: center; justify-content: space-between; }
    .q-brand { display: flex; align-items: center; gap: 14px; text-decoration: none; color: inherit; }
    .q-brand-logo { width: 55px; height: 55px; border-radius: 12px; overflow: hidden; border: 1px solid var(--q-border); }
    .q-brand-logo img { width: 100%; height: 100%; object-fit: contain; }
    .q-brand h2 { margin: 0; color: var(--q-guinda); font-size: 22px; font-weight: 800; }
    .q-nav a { padding: 10px 15px; border-radius: 10px; font-weight: 700; color: #4b5563; text-decoration: none; transition: 0.2s; }
    .q-nav a:hover { background: #f3f4f6; color: var(--q-guinda); }

    .q-page-shell { padding: 40px 0 80px; }
    .q-page-header { margin-bottom: 30px; }
    .q-page-header h1 { margin: 0 0 10px; color: var(--q-guinda); font-size: 30px; font-weight: 800; }
    .q-page-header p { margin: 0; color: #6b7280; font-size: 16px; }

    .q-layout { display: grid; grid-template-columns: 1fr 380px; gap: 25px; align-items: stretch; }
    .q-form-card { background: #fff; border: 1px solid var(--q-border); border-radius: 24px; padding: 35px; box-shadow: var(--q-shadow); }

    .q-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .q-group { display: flex; flex-direction: column; gap: 8px; }
    .q-full { grid-column: 1 / -1; }

    label { font-weight: 700; color: #374151; font-size: 14px; display: flex; align-items: center; gap: 5px; }
    input, textarea, select {
      width: 100%; min-height: 48px; border: 1px solid #d1d5db;
      border-radius: 12px; padding: 12px 16px; font: inherit; background: #fff; box-sizing: border-box;
    }
    input:focus, textarea:focus, select:focus { outline: none; border-color: var(--q-guinda); box-shadow: 0 0 0 4px rgba(122,23,55,0.08); }

    .q-radio-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 5px; }
    .q-radio-item {
      display: flex; align-items: center; justify-content: flex-start; gap: 12px;
      border: 1px solid var(--q-border); background: #f9fafb; border-radius: 12px;
      padding: 14px; cursor: pointer; font-weight: 600; font-size: 13px; transition: 0.2s;
    }
    .q-radio-item input { width: 18px; height: 18px; min-height: auto; cursor: pointer; margin: 0; }
    .q-radio-item:hover { border-color: var(--q-guinda); background: #fff; }

    .q-side-images { display: flex; flex-direction: column; gap: 20px; }
    .q-img-box { background: #fff; border: 1px solid var(--q-border); border-radius: 24px; overflow: hidden; position: relative; box-shadow: var(--q-shadow); }
    .q-img-main { flex: 2; min-height: 450px; }
    .q-img-main img { width: 100%; height: 100%; object-fit: cover; object-position: center 15%; display: block; }
    .q-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(122,23,55,0.1), rgba(122,23,55,0.4)); pointer-events: none; }

    .q-actions { margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px; }
    .q-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; padding: 0 30px; border-radius: 12px; background: var(--q-guinda); color: #fff; font-weight: 700; border: none; cursor: pointer; text-decoration: none; transition: 0.2s; }
    .q-btn-light { background: #f3f4f6; color: #374151; }
    .q-btn:hover { opacity: .9; transform: translateY(-1px); }

    .q-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
    .q-modal { background: #fff; border-radius: 24px; padding: 35px; width: min(480px, 90%); text-align: center; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
    .q-modal h3 { font-size: 24px; margin-bottom: 10px; }

    @media (max-width: 1000px) {
      .q-layout { grid-template-columns: 1fr; }
      .q-grid, .q-radio-grid { grid-template-columns: 1fr; }
      .q-img-main { height: 300px; min-height: auto; }
    }
  </style>
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/index.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/solicitudes.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/public-footer.css').'?v=2' }}">
</head>
<body>
<header class="q-topbar">
  <div class="q-wrap q-topbar-inner">
    <a href="{{ route('home') }}" class="q-brand">
      <div class="q-brand-logo"><img src="{{ asset('assets/img/legislatura3.png') }}" alt="Logo"></div>
      <div><h2>Samuel Te Escucha</h2></div>
    </a>
    <nav class="q-nav">
      <a href="{{ route('home') }}">Inicio</a>
      <a href="{{ route('solicitudes.create', 'citas') }}">Citas</a>
      <a href="{{ route('solicitudes.create', 'apoyos') }}">Apoyos</a>
    </nav>
  </div>
</header>

<main class="q-wrap q-page-shell">
  @include('solicitudes.partials.feedback')
  <div class="q-page-header">
    <h1>Tu solicitud será canalizada directamente con las autoridades correspondientes.</h1>
    <p>Completa el formulario para recibir atención por parte de nuestro equipo.</p>
  </div>

  <section class="q-layout">
    <div class="q-form-card">
      <form method="POST" action="{{ route('solicitudes.store', 'quejas') }}" enctype="multipart/form-data">
      @csrf
        <div class="q-grid">

          <div class="q-group">
            <label for="nombre">Nombre</label>
            <input id="nombre" type="text" name="nombre" required value="{{ old('nombre') }}">
          </div>
          <div class="q-group">
            <label for="apellido_paterno">Apellido paterno</label>
            <input id="apellido_paterno" type="text" name="apellido_paterno" required value="{{ old('apellido_paterno') }}">
          </div>
          <div class="q-group">
            <label for="apellido_materno">Apellido materno</label>
            <input id="apellido_materno" type="text" name="apellido_materno" required value="{{ old('apellido_materno') }}">
          </div>

          <div class="q-group">
            <label for="celular_1">Teléfono celular</label>
            <input id="celular_1" type="text" name="celular_1" maxlength="10" pattern="[0-9]{10}" required value="{{ old('celular_1') }}">
          </div>
          <div class="q-group">
            <label for="correo">Correo electrónico</label>
            <input id="correo" type="email" name="correo" required value="{{ old('correo') }}">
          </div>

          <div class="q-group">
            <label for="calle">Calle</label>
            <input id="calle" type="text" name="calle" required value="{{ old('calle') }}">
          </div>
          <div class="q-group">
            <label for="no_exterior">No. exterior</label>
            <input id="no_exterior" type="text" name="no_exterior" required value="{{ old('no_exterior') }}">
          </div>

          <div class="q-group">
            <label for="codigo_postal">Código postal</label>
            <input
              type="text"
              id="codigo_postal" data-lookup-url="{{ route('cp.lookup') }}"
              name="codigo_postal"
              maxlength="5"
              pattern="[0-9]{5}"
              inputmode="numeric"
              placeholder="Ej: 55803"
              required
              oninput="this.value = this.value.replace(/[^0-9]/g,'').slice(0,5);"
             value="{{ old('codigo_postal') }}">
          </div>

          <div class="q-group">
            <label for="colonia">Colonia</label>
            <select id="colonia" data-old="{{ old('colonia') }}" name="colonia" required>
              <option value="">Seleccione una colonia</option>
            </select>
          </div>

          <div class="q-group">
            <label for="municipio">Municipio</label>
            <input type="text" id="municipio" name="municipio" readonly required value="{{ old('municipio') }}">
          </div>

          <div class="q-group q-full">
            <label>Tipo de reporte</label>
            <div class="q-radio-grid">
              <label class="q-radio-item"><input type="radio" name="tipo" value="Seguridad" required @checked(old('tipo') === 'Seguridad')> Seguridad pública</label>
              <label class="q-radio-item"><input type="radio" name="tipo" value="Alumbrado" @checked(old('tipo') === 'Alumbrado')> Alumbrado público</label>
              <label class="q-radio-item"><input type="radio" name="tipo" value="Agua" @checked(old('tipo') === 'Agua')> Abasto de agua</label>
              <label class="q-radio-item"><input type="radio" name="tipo" value="Basura" @checked(old('tipo') === 'Basura')> Recolección de basura</label>
              <label class="q-radio-item"><input type="radio" name="tipo" value="Calles" @checked(old('tipo') === 'Calles')> Estado de calles</label>
              <label class="q-radio-item"><input type="radio" name="tipo" value="Áreas verdes" @checked(old('tipo') === 'Áreas verdes')> Áreas verdes</label>
              <label class="q-radio-item"><input type="radio" name="tipo" value="Otro" @checked(old('tipo') === 'Otro')> Otro</label>
            </div>
          </div>

          <div class="q-group q-full">
            <label for="descripcion">Descripción detallada</label>
            <textarea id="descripcion" name="descripcion" maxlength="500" rows="5" required placeholder="Describe brevemente la situación...">{{ old('descripcion') }}</textarea>
          </div>

          <div class="q-group q-full">
            <label for="evidencia">Adjuntar evidencia (foto)</label>
            <input id="evidencia" type="file" name="evidencia" accept="image/jpeg,image/png,image/webp" required>
          </div>

        </div>

        <div class="q-actions">
          <a href="{{ route('home') }}" class="q-btn q-btn-light">Volver al inicio</a>
          <button type="submit" class="q-btn">Enviar reporte</button>
        </div>
      </form>
    </div>

    <aside class="q-side-images">
      <div class="q-img-box q-img-main">
        <div class="q-overlay"></div>
        <img src="{{ asset('assets/img/ISamuel12.png') }}" alt="Samuel">
      </div>
    </aside>
  </section>
</main>

<x-public-footer />
<script src="{{ asset('assets/js/solicitudes.js') }}" defer></script>
<x-chatbot />
</body>
</html>

