<?php

namespace Database\Seeders;

use App\Models\CodigoPostal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodigosPostalesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = json_decode(file_get_contents(database_path('data/codigos_postales.json')), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($rows): void {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::statement('LOCK TABLE codigos_postales IN SHARE ROW EXCLUSIVE MODE');
            }
            foreach ($rows as $row) {
                CodigoPostal::firstOrCreate($row);
            }
        });
    }
}
