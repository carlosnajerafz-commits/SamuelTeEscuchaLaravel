@extends('adminlte::page')
@section('title', 'Panel de empleados')
@section('content_header')<h1>Samuel Te Escucha</h1>@endsection
@section('content')
<p>Bienvenido, {{ auth()->user()->nombre_completo }}.</p>
@php($moduleIcons = ['quejas' => 'bi bi-megaphone', 'citas' => 'bi bi-calendar-check', 'apoyos' => 'bi bi-box2-heart', 'comedor' => 'bi bi-cup-hot'])
@php($moduleThemes = ['quejas' => 'danger', 'citas' => 'primary', 'apoyos' => 'success', 'comedor' => 'warning'])
<div class="row">
@foreach($summaries as $key => $summary)
    <div class="col-md-6 col-xl-3">
        <x-adminlte-small-box :title="$summary['pending']" :text="$summary['title'].' por atender'" :icon="$moduleIcons[$key]" :theme="$moduleThemes[$key]" :url="route('admin.solicitudes.index', ['module' => $key, 'estatus' => config('samuel.modules.'.$key.'.statuses')[0]])" url-text="Ver pendientes"/>
        <p class="small text-body-secondary mt-n2 mb-3">{{ $summary['total'] }} en total · {{ $summary['today'] }} recibidas hoy</p>
    </div>
@endforeach
</div>
<div class="row">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h5 mb-0">Próximas citas</h2></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-sm table-striped mb-0">
                <thead><tr><th>Fecha</th><th>Hora</th><th>Solicitante</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($upcomingAppointments as $appointment)
                    <tr><td>{{ $appointment->fecha }}</td><td>{{ substr($appointment->hora, 0, 5) }}</td><td><a href="{{ route('admin.solicitudes.show', ['citas', $appointment->id]) }}">{{ $appointment->nombre }} {{ $appointment->apellido_paterno }}</a></td><td>@include('admin.status', ['status' => $appointment->estatus])</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center py-3">No hay citas próximas.</td></tr>
                @endforelse
                </tbody>
            </table></div></div>
            <div class="card-footer"><a href="{{ route('admin.solicitudes.index', 'citas') }}">Todas las citas</a> · <a href="{{ route('admin.bloqueos.index') }}">Bloqueos de calendario</a></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header"><h2 class="card-title h5 mb-0">Próximos eventos de comedor</h2></div>
            <ul class="list-group list-group-flush">
            @forelse($upcomingEvents as $event)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between"><strong>{{ $event->fecha }} · {{ substr($event->hora_inicio, 0, 5) }}</strong><span>{{ (int) $event->inscritos }} / {{ $event->cupo_maximo > 0 ? $event->cupo_maximo : 'sin límite' }}</span></div>
                    <div class="small text-body-secondary">{{ $event->lugar }}</div>
                    @if($event->cupo_maximo > 0)
                        <div class="progress mt-1" role="progressbar" aria-label="Ocupación" aria-valuenow="{{ (int) $event->inscritos }}" aria-valuemin="0" aria-valuemax="{{ $event->cupo_maximo }}" style="height:6px"><div class="progress-bar" style="width: {{ min(100, round((int) $event->inscritos * 100 / $event->cupo_maximo)) }}%"></div></div>
                    @endif
                </li>
            @empty
                <li class="list-group-item text-center py-3">No hay eventos activos próximos.</li>
            @endforelse
            </ul>
            <div class="card-footer"><a href="{{ route('admin.catalogos.index', 'comedor') }}">Administrar eventos</a></div>
        </div>
    </div>
</div>
@endsection
