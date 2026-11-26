@php($statusTheme = match ($status) { 'pendiente', 'solicitada' => 'warning', 'en proceso', 'aceptada', 'confirmado' => 'primary', 'atendida', 'entregado', 'realizada', 'completada', 'asistio' => 'success', 'rechazada', 'cancelada', 'cancelado' => 'danger', default => 'secondary' })
<span class="badge text-bg-{{ $statusTheme }}">{{ $label ?? $status }}</span>
