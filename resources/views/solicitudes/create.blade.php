@extends('layouts.public')
@section('title', $definition['title'])
@section('content')
<h1>{{ $definition['title'] }}</h1>
@if(in_array($module, ['apoyos', 'comedor']) && $options->isEmpty())
<p>Por el momento no hay {{ $module === 'apoyos' ? 'apoyos' : 'eventos' }} disponibles.</p>
@else
<form method="POST" action="{{ route('solicitudes.store', $module) }}" enctype="multipart/form-data" class="form-card">
@csrf
@if($module === 'apoyos')
<label for="apoyo">Apoyo</label><select id="apoyo" name="apoyo" required><option value="">Selecciona un apoyo</option>@foreach($options as $option)<option value="{{ $option->nombre }}" @selected(old('apoyo') === $option->nombre)>{{ $option->nombre }} — {{ $option->descripcion }}</option>@endforeach</select>
@elseif($module === 'comedor')
<label for="evento_id">Evento</label><select id="evento_id" name="evento_id" required><option value="">Selecciona un evento</option>@foreach($options as $option)<option value="{{ $option->id }}" @selected(old('evento_id') == $option->id)>{{ $option->fecha }} · {{ $option->hora_inicio }} · {{ $option->lugar }} · {{ $option->menu }}</option>@endforeach</select>
@endif
<fieldset><legend>Datos del solicitante</legend><div class="grid">
@foreach(['nombre'=>'Nombre', 'apellido_paterno'=>'Apellido paterno', 'apellido_materno'=>'Apellido materno', 'celular_1'=>'Celular principal', 'celular_2'=>'Celular alternativo', 'correo'=>'Correo electrónico', 'calle'=>'Calle', 'no_exterior'=>'Número exterior', 'no_interior'=>'Número interior'] as $field => $label)
<div><label for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" type="{{ $field === 'correo' ? 'email' : (str_starts_with($field, 'celular') ? 'tel' : 'text') }}" @required(!in_array($field, ['no_interior', 'celular_2', 'correo']) || ($field === 'celular_2' && in_array($module, ['apoyos', 'citas'])) || ($field === 'correo' && $module !== 'comedor'))></div>
@endforeach
<div><label for="codigo_postal">Código postal</label><input id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal') }}" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" required></div>
<div><label for="colonia">Colonia</label><select id="colonia" name="colonia" required><option value="">Captura tu código postal</option></select></div>
<div><label for="municipio">Municipio</label><input id="municipio" name="municipio" value="{{ old('municipio') }}" readonly required></div>
@if($module !== 'citas')<div><label for="seccion_electoral">Sección electoral</label><input id="seccion_electoral" name="seccion_electoral" value="{{ old('seccion_electoral') }}" maxlength="10" inputmode="numeric"></div>@endif
</div><p id="cp-status" role="status"></p></fieldset>
@if($module === 'quejas')
<label for="tipo">Tipo de gestión</label><input id="tipo" name="tipo" value="{{ old('tipo') }}" maxlength="150" required>
<label for="descripcion">Descripción (máximo 500 caracteres)</label><textarea id="descripcion" name="descripcion" maxlength="500" required>{{ old('descripcion') }}</textarea>
<label for="evidencia">Evidencia: JPG, PNG o WEBP, máximo 5 MB</label><input type="file" id="evidencia" name="evidencia" accept="image/jpeg,image/png,image/webp" required>
@elseif($module === 'citas')
<label for="fecha">Fecha (martes, a partir de mañana)</label><input type="date" id="fecha" name="fecha" value="{{ old('fecha') }}" min="{{ today()->addDay()->toDateString() }}" required>
<label for="hora">Horario disponible</label><select id="hora" name="hora" required><option value="">Selecciona una fecha</option></select><p id="cita-status" role="status"></p>
<label for="motivo">Motivo de la cita</label><textarea id="motivo" name="motivo" maxlength="2000">{{ old('motivo') }}</textarea>
@else
@if($module === 'comedor')
<label for="numero_personas">Número de personas</label><input type="number" id="numero_personas" name="numero_personas" min="1" max="20" value="{{ old('numero_personas', 1) }}" required>
<label for="categoria_vulnerable">Grupo al que perteneces</label><select id="categoria_vulnerable" name="categoria_vulnerable" required><option value="">Selecciona una categoría</option>@foreach(config('samuel.categories') as $category)<option @selected(old('categoria_vulnerable') === $category)>{{ $category }}</option>@endforeach</select>
@endif
<label for="observaciones">Observaciones</label><textarea id="observaciones" name="observaciones" maxlength="500">{{ old('observaciones') }}</textarea>
@endif
<button class="btn" type="submit">Registrar solicitud</button>
</form>
@endif
@endsection
@push('scripts')
<script>
(() => {
 const cp = document.getElementById('codigo_postal'); if (!cp) return;
 const colony = document.getElementById('colonia'), town = document.getElementById('municipio'), status = document.getElementById('cp-status');
 let entries = [], serial = 0;
 async function lookup() {
   const current = ++serial; colony.replaceChildren(new Option('Selecciona una colonia', '')); town.value = ''; entries = [];
   if (!/^\d{5}$/.test(cp.value)) return;
   status.textContent = 'Buscando colonias…';
   try {
     const res = await fetch(@json(route('cp.lookup')) + '?cp=' + encodeURIComponent(cp.value), {headers: {'Accept':'application/json'}});
     if (!res.ok) throw new Error(); const data = await res.json(); if (current !== serial) return;
     entries = data.resultados; entries.forEach(row => colony.add(new Option(row.colonia, row.colonia)));
     colony.value = @json(old('colonia', '')); town.value = (entries.find(row => row.colonia === colony.value) || {}).municipio || '';
     status.textContent = entries.length ? '' : 'Código postal no encontrado. Contacta al personal para actualizar el catálogo.';
   } catch { if (current === serial) status.textContent = 'No fue posible consultar las colonias. Intenta de nuevo.'; }
 }
 cp.addEventListener('input', lookup); colony.addEventListener('change', () => town.value = (entries.find(row => row.colonia === colony.value) || {}).municipio || ''); lookup();
 const date = document.getElementById('fecha'); if (!date) return;
 const hour = document.getElementById('hora'), message = document.getElementById('cita-status'); let dateSerial = 0;
 async function availability() {
   const current = ++dateSerial; hour.replaceChildren(new Option('Selecciona un horario', '')); if (!date.value) return;
   message.textContent = 'Consultando disponibilidad…';
   try {
     const res = await fetch(@json(route('citas.availability')) + '?fecha=' + encodeURIComponent(date.value), {headers: {'Accept':'application/json'}});
     if (!res.ok) throw new Error(); const data = await res.json(); if (current !== dateSerial) return;
     data.disponibles.forEach(slot => hour.add(new Option(slot, slot))); hour.value = @json(old('hora', ''));
     message.textContent = data.disponibles.length ? '' : 'No hay horarios disponibles en esta fecha.';
   } catch { if(current === dateSerial) message.textContent = 'Elige un martes posterior a hoy. Si el problema continúa, intenta de nuevo.'; }
 }
 date.addEventListener('change', availability); availability();
})();
</script>
@endpush