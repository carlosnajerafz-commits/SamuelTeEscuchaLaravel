<?php

namespace Tests\Feature;

use App\Models\CodigoPostal;
use App\Models\Empleado;
use Tests\LegacyDatabaseTestCase;

class CodigoPostalTest extends LegacyDatabaseTestCase
{
    public function test_lookup_preserves_leading_zeroes_and_colony_municipality_relationship(): void
    {
        CodigoPostal::create(['codigo_postal' => '01234', 'colonia' => 'Centro', 'municipio' => 'Tecámac']);
        $this->getJson('/buscar-cp?cp=01234')->assertOk()->assertJsonPath('resultados.0.municipio', 'Tecámac')->assertJsonPath('colonias.0', 'Centro');
        $this->getJson('/buscar-cp?cp=00000')->assertOk()->assertJsonPath('resultados', []);
        $this->getJson('/buscar-cp?cp=1234')->assertUnprocessable();
    }

    public function test_guests_cannot_read_admin_or_change_catalog(): void
    {
        $this->get('/admin/codigos-postales')->assertRedirect('/login');
        $this->post('/admin/codigos-postales', [])->assertRedirect('/login');
        $this->put('/admin/codigos-postales/1', [])->assertRedirect('/login');
        $this->delete('/admin/codigos-postales/1')->assertRedirect('/login');
    }

    public function test_employee_can_create_edit_and_delete_colony_without_timestamp_columns(): void
    {
        $this->actingAs(Empleado::factory()->create());
        $data = ['codigo_postal' => '55740', 'colonia' => 'Centro', 'municipio' => 'Tecámac'];
        $this->post('/admin/codigos-postales', $data)->assertRedirect('/admin/codigos-postales');
        $code = CodigoPostal::firstOrFail();
        $this->get('/admin/codigos-postales/'.$code->id.'/edit')->assertOk();
        $this->put('/admin/codigos-postales/'.$code->id, [...$data, 'colonia' => 'Nueva'])->assertRedirect('/admin/codigos-postales');
        $this->assertDatabaseHas('codigos_postales', ['colonia' => 'Nueva']);
        $this->get('/admin/codigos-postales')->assertOk()->assertSee('Nueva');
        $this->delete('/admin/codigos-postales/'.$code->id)->assertRedirect('/admin/codigos-postales');
        $this->assertDatabaseCount('codigos_postales', 0);
    }

    public function test_invalid_catalog_data_is_rejected(): void
    {
        $this->actingAs(Empleado::factory()->create())->post('/admin/codigos-postales', ['codigo_postal' => '123', 'colonia' => '', 'municipio' => ''])->assertSessionHasErrors(['codigo_postal', 'colonia', 'municipio']);
        $this->assertDatabaseCount('codigos_postales', 0);
    }
}
