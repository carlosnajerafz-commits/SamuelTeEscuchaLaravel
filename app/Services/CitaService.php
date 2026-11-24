<?php

namespace App\Services;

use App\Models\BloqueoCita;
use App\Models\Cita;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CitaService
{
    /** Serialize all Laravel bookings and blocks for a day, including empty slots. */
    public function lockDay(string $date): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::select('SELECT pg_advisory_xact_lock(?, ?)', [73001, (int) str_replace('-', '', $date)]);
        }
    }

    public function ensureAvailable(string $date, string $time, ?int $except = null): void
    {
        $blocked = BloqueoCita::where('fecha', $date)->where(fn ($query) => $query->where('dia_completo', true)->orWhere('hora', $time))->exists();
        $occupied = Cita::where('fecha', $date)->where('hora', $time)->whereIn('estatus', ['solicitada', 'aceptada'])->when($except, fn ($query) => $query->where('id', '!=', $except))->exists();
        if ($blocked || $occupied) {
            throw ValidationException::withMessages(['hora' => 'La fecha u hora seleccionada ya no está disponible.']);
        }
    }

    public function reserve(array $data): Cita
    {
        return DB::transaction(function () use ($data) {
            $this->lockDay($data['fecha']);
            $this->ensureAvailable($data['fecha'], $data['hora'].':00');

            return Cita::create([...$data, 'hora' => $data['hora'].':00', 'estatus' => 'solicitada', 'ine_path' => null]);
        });
    }

    public function available(string $date): array
    {
        if (BloqueoCita::where('fecha', $date)->where('dia_completo', true)->exists()) {
            return [];
        }
        $occupied = Cita::where('fecha', $date)->whereIn('estatus', ['solicitada', 'aceptada'])->pluck('hora')->merge(BloqueoCita::where('fecha', $date)->whereNotNull('hora')->pluck('hora'))->map(fn ($hour) => substr($hour, 0, 5))->all();

        return array_values(array_diff(config('samuel.slots'), $occupied));
    }
}
