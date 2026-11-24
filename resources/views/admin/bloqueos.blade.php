@extends('adminlte::page')
@section('content_header')<h1>Bloqueos de citas</h1>@endsection
@section('content')
@include('admin.messages')
<form method="POST" action="{{ route('admin.bloqueos.store') }}">@csrf
<label for="fecha">Fecha</label><input class="form-control mb-3" id="fecha" type="date" name="fecha" value="{{ old('fecha') }}" min="{{ today()->toDateString() }}" required>
<label for="dia_completo">Tipo</label><select class="form-select mb-3" id="dia_completo" name="dia_completo"><option value="1">Día completo</option><option value="0" @selected(old('dia_completo') === '0')>Horarios seleccionados</option></select>
<fieldset><legend class="h6">Horarios (solo para bloqueo por horas)</legend><div class="d-flex flex-wrap gap-3">@foreach(config('samuel.slots') as $slot)<label><input type="checkbox" name="horas[]" value="{{ $slot }}" @checked(in_array($slot, old('horas', [])))> {{ $slot }}</label>@endforeach</div></fieldset>
<label for="motivo">Motivo</label><input class="form-control mb-3" id="motivo" name="motivo" maxlength="255" value="{{ old('motivo') }}"><button class="btn btn-primary mb-4">Guardar bloqueo</button></form>
<table class="table"><thead><tr><th>Fecha</th><th>Hora</th><th>Motivo</th><th>Acción</th></tr></thead><tbody>@foreach($blocks as $block)<tr><td>{{ $block->fecha }}</td><td>{{ $block->dia_completo ? 'Día completo' : $block->hora }}</td><td>{{ $block->motivo }}</td><td><form method="POST" action="{{ route('admin.bloqueos.destroy', $block->id) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger">Desbloquear</button></form></td></tr>@endforeach</tbody></table>{{ $blocks->links() }}
@endsection