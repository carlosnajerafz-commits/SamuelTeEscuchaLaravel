<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apoyos | Samuel Te Escucha</title>
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/solicitudes.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/public-footer.css').'?v=2' }}">
</head>
<body>
<header class="topbar">
  <div class="wrap topbar__inner">
    <div class="brand">
      <div class="brand__logo">
        <img src="{{ asset('assets/img/legislatura3.png') }}" alt="Samuel Te Escucha">
      </div>
      <div>
        <h2>Samuel Te Escucha</h2>
      </div>
    </div>
    <nav class="nav">
      <a href="{{ route('home') }}">Inicio</a>
      <a href="{{ route('solicitudes.create', 'quejas') }}">Gestión</a>
      <a href="{{ route('solicitudes.create', 'citas') }}">Citas</a>
      <a href="{{ route('solicitudes.create', 'comedor') }}">Comedor</a>
    </nav>
  </div>
</header>

<main class="wrap page-shell">
  @include('solicitudes.partials.feedback')

  <section class="form-card">
    <form method="POST" action="{{ route('solicitudes.store', 'apoyos') }}" enctype="multipart/form-data">
      @csrf
      <div class="form-grid">

        <!-- APOYO DISPONIBLE -->
        <div class="form-group form-group--full">
          <label class="help-inline">
            Apoyo disponible
            <button type="button" class="help-btn" onclick="document.getElementById('modalApoyos').style.display='flex'">?</button>
          </label>
          <div class="radio-grid">
@if ($options->isEmpty())<p>No hay apoyos activos por el momento.</p>@endif
            @foreach ($options as $apoyo)
              <label class="radio-item">
                <input type="radio" name="apoyo" value="{{ $apoyo->nombre }}" @checked(old('apoyo') === $apoyo->nombre) required>
                {{ $apoyo->nombre }}
              </label>
            @endforeach
          </div>
        </div>

        <div class="form-group">
          <label for="nombre">Nombre</label>
          <input id="nombre" type="text" name="nombre" required value="{{ old('nombre') }}">
        </div>

        <div class="form-group">
          <label for="apellido_paterno">Apellido paterno</label>
          <input id="apellido_paterno" type="text" name="apellido_paterno" required value="{{ old('apellido_paterno') }}">
        </div>

        <div class="form-group">
          <label for="apellido_materno">Apellido materno</label>
          <input id="apellido_materno" type="text" name="apellido_materno" required value="{{ old('apellido_materno') }}">
        </div>

        <div class="form-group">
          <label for="celular_1">Celular 1</label>
          <input id="celular_1" type="text" name="celular_1" maxlength="10" pattern="[0-9]{10}" required value="{{ old('celular_1') }}">
        </div>

        <div class="form-group">
          <label for="celular_2">Celular 2</label>
          <input id="celular_2" type="text" name="celular_2" maxlength="10" pattern="[0-9]{10}" required value="{{ old('celular_2') }}">
        </div>

        <div class="form-group">
          <label for="correo">Correo electrónico</label>
          <input id="correo" type="email" name="correo" required value="{{ old('correo') }}">
        </div>

        <div class="form-group">
          <label for="calle">Calle</label>
          <input id="calle" type="text" name="calle" required value="{{ old('calle') }}">
        </div>

        <div class="form-group">
          <label for="no_exterior">No. exterior</label>
          <input id="no_exterior" type="text" name="no_exterior" required value="{{ old('no_exterior') }}">
        </div>

        <div class="form-group">
          <label for="no_interior">No. interior</label>
          <input id="no_interior" type="text" name="no_interior" value="{{ old('no_interior') }}">
        </div>

        <div class="form-group">
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

        <div class="form-group">
          <label for="colonia">Colonia</label>
          <select id="colonia" data-old="{{ old('colonia') }}" name="colonia" required>
            <option value="">Seleccione una colonia</option>
          </select>
        </div>

        <div class="form-group">
          <label for="municipio">Municipio</label>
          <input type="text" id="municipio" name="municipio" readonly required value="{{ old('municipio') }}">
        </div>

        <div class="form-group form-group--full">
          <label for="observaciones">Observaciones</label>
          <textarea id="observaciones" name="observaciones" maxlength="500">{{ old('observaciones') }}</textarea>
        </div>

      </div>

      <div class="form-actions">
        <a href="{{ route('home') }}" class="btn btn--light">Volver</a>
        <button type="submit" class="btn" @disabled($options->isEmpty())>Enviar solicitud</button>
      </div>
    </form>
  </section>

  <!-- Modal apoyos disponibles -->
  <div id="modalApoyos" class="status-modal-overlay" style="display:none;">
    <div class="status-modal modal-apoyos">
      <h3>Apoyos disponibles</h3>
      <p>Aquí puedes consultar los apoyos activos y su descripción.</p>
      <div class="apoyos-help-list">
        @if ($options->isEmpty())
          <div class="apoyo-help-item"><strong>No hay apoyos activos</strong></div>
        @else
          @foreach ($options as $apoyo)
            <div class="apoyo-help-item">
              <strong>{{ $apoyo->nombre }}</strong>
              @if ($apoyo->descripcion)
                <p>{{ $apoyo->descripcion }}</p>
              @else
                <p>Sin descripción disponible.</p>
              @endif
            </div>
          @endforeach
        @endif
      </div>
      <button type="button" class="btn" onclick="document.getElementById('modalApoyos').style.display='none'">Cerrar</button>
    </div>
  </div>

</main>

<x-public-footer />
<script src="{{ asset('assets/js/solicitudes.js') }}" defer></script>
<x-chatbot />
</body>
</html>

