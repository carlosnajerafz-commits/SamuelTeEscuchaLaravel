<?php

namespace Tests\Feature;

use App\Models\CodigoPostal;
use Database\Seeders\CodigosPostalesSeeder;
use Tests\LegacyDatabaseTestCase;

class CodigosPostalesSeederTest extends LegacyDatabaseTestCase
{
    public function test_catalog_preserves_existing_rows_and_can_be_loaded_twice(): void
    {
        $existing = CodigoPostal::create(['codigo_postal' => '55740', 'colonia' => 'Colonia existente', 'municipio' => 'Tecámac']);
        CodigoPostal::create(['codigo_postal' => '55803', 'colonia' => 'Acatitla', 'municipio' => 'Teotihuacán']);
        $this->seed(CodigosPostalesSeeder::class);
        $count = CodigoPostal::count();
        $this->seed(CodigosPostalesSeeder::class);
        $this->assertSame($count, CodigoPostal::count());
        $this->assertModelExists($existing);
        $this->assertSame(64, CodigoPostal::distinct()->count('codigo_postal'));
        $this->assertSame(1, CodigoPostal::where('codigo_postal', '55803')->where('colonia', 'Acatitla')->count());
        $this->getJson('/buscar-cp?cp=55803')->assertOk()->assertJsonPath('colonias', ['Acatitla', 'Colatitla', 'El Tejocote']);
        $this->getJson('/buscar-cp?cp=55998')->assertOk()->assertJsonPath('resultados.0.municipio', 'Temascalapa');
    }
}
