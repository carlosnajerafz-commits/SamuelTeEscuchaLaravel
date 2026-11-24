<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class InspectLegacyDatabase extends Command
{
    protected $signature = 'samuel:inspect-database';

    protected $description = 'Consulta tablas y columnas del sistema actual sin modificar datos.';

    public function handle(): int
    {
        foreach (['empleados', 'quejas', 'citas', 'bloqueos_cita', 'apoyos', 'registros_apoyos', 'eventos_comedor', 'registros_comedor', 'codigos_postales', 'almacenes', 'migrations'] as $table) {
            $this->line($table.': '.(Schema::hasTable($table) ? implode(', ', Schema::getColumnListing($table)) : 'NO EXISTE'));
        }

        return self::SUCCESS;
    }
}
