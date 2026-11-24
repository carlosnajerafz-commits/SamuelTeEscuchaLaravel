<?php

namespace Tests\Feature;

use App\Models\Apoyo;
use App\Models\BloqueoCita;
use App\Models\Cita;
use App\Models\Empleado;
use App\Models\EventoComedor;
use App\Models\Queja;
use App\Models\RegistroComedor;
use Laravel\Sanctum\Sanctum;
use Tests\LegacyDatabaseTestCase;

class ApiTest extends LegacyDatabaseTestCase
{
    /** @return array<string, string> */
    private function ciudadano(): array
    {
        return ['nombre' => 'Prueba', 'apellido_paterno' => 'Ciudadano', 'apellido_materno' => 'Local', 'celular_1' => '5512345678', 'celular_2' => '5587654321', 'correo' => 'prueba@example.test', 'calle' => 'Principal', 'no_exterior' => '10', 'colonia' => 'Centro', 'municipio' => 'Tecámac', 'codigo_postal' => '55740'];
    }

    private function cita(string $hora = '09:00:00', string $estatus = 'solicitada'): Cita
    {
        return Cita::create([...$this->ciudadano(), 'fecha' => today()->next('Tuesday')->toDateString(), 'hora' => $hora, 'estatus' => $estatus, 'ine_path' => 'privado/ine.jpg']);
    }

    public function test_protected_routes_reject_requests_without_token(): void
    {
        foreach (['/api/v1/citas', '/api/v1/quejas', '/api/v1/registros-apoyo', '/api/v1/comedor/registros', '/api/v1/estadisticas', '/api/v1/me'] as $ruta) {
            $this->getJson($ruta)->assertUnauthorized();
        }
    }

    public function test_token_is_issued_with_valid_credentials_and_works(): void
    {
        Empleado::factory()->create(['usuario' => 'operador']);

        $token = $this->postJson('/api/v1/token', ['usuario' => ' OPERADOR ', 'password' => 'prueba-segura'])
            ->assertCreated()
            ->assertJsonPath('datos.tipo', 'Bearer')
            ->assertJsonPath('datos.empleado.usuario', 'operador')
            ->json('datos.token');

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('datos.usuario', 'operador');
    }

    public function test_token_is_denied_with_wrong_password_or_unknown_user(): void
    {
        Empleado::factory()->create(['usuario' => 'operador']);

        $this->postJson('/api/v1/token', ['usuario' => 'operador', 'password' => 'otra'])->assertUnprocessable();
        $this->postJson('/api/v1/token', ['usuario' => 'nadie', 'password' => 'prueba-segura'])->assertUnprocessable();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_revoked_token_stops_working(): void
    {
        $empleado = Empleado::factory()->create();
        $token = $empleado->createToken('prueba')->plainTextToken;

        $this->withToken($token)->deleteJson('/api/v1/token')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_appointments_list_hides_document_paths(): void
    {
        Sanctum::actingAs(Empleado::factory()->create());
        $cita = $this->cita();

        $this->getJson('/api/v1/citas')
            ->assertOk()
            ->assertJsonPath('data.0.folio', 'CITA-'.str_pad((string) $cita->id, 6, '0', STR_PAD_LEFT))
            ->assertJsonPath('data.0.tiene_identificacion', true)
            ->assertDontSee('privado/ine.jpg');
    }

    public function test_status_update_rechecks_slot_availability(): void
    {
        Sanctum::actingAs(Empleado::factory()->create());
        $cancelada = $this->cita('09:00:00', 'cancelada');
        $this->cita('09:00:00', 'solicitada');

        $this->patchJson("/api/v1/citas/{$cancelada->id}/estatus", ['estatus' => 'aceptada'])->assertUnprocessable();
        $this->assertSame('cancelada', $cancelada->fresh()->estatus);

        $this->patchJson("/api/v1/citas/{$cancelada->id}/estatus", ['estatus' => 'realizada'])
            ->assertOk()
            ->assertJsonPath('datos.estatus_anterior', 'cancelada')
            ->assertJsonPath('datos.estatus_actual', 'realizada');
    }

    public function test_status_must_be_one_of_the_configured_values(): void
    {
        Sanctum::actingAs(Empleado::factory()->create());
        $queja = Queja::create([...$this->ciudadano(), 'tipo' => 'Alumbrado', 'descripcion' => 'Luminaria apagada']);

        $this->patchJson("/api/v1/quejas/{$queja->id}/estatus", ['estatus' => 'inventado'])->assertUnprocessable();
    }

    public function test_public_supports_catalog_only_lists_active(): void
    {
        Apoyo::create(['nombre' => 'Despensa', 'activo' => true]);
        Apoyo::create(['nombre' => 'Programa cerrado', 'activo' => false]);

        $this->getJson('/api/v1/apoyos')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.nombre', 'Despensa');
    }

    public function test_public_comedor_events_report_remaining_capacity(): void
    {
        $evento = EventoComedor::create(['fecha' => today()->addDay()->toDateString(), 'hora_inicio' => '12:00:00', 'hora_fin' => '15:00:00', 'cupo_maximo' => 10, 'activo' => true]);
        RegistroComedor::create([...$this->ciudadano(), 'evento_id' => $evento->id, 'numero_personas' => 4]);

        $this->getJson('/api/v1/comedor/eventos')->assertOk()->assertJsonPath('data.0.cupo.disponible', 6);
    }

    public function test_availability_respects_bookings_and_blocks(): void
    {
        $martes = today()->next('Tuesday');
        $this->cita('09:00:00');
        BloqueoCita::create(['fecha' => $martes->toDateString(), 'hora' => '09:20:00']);

        $libres = $this->getJson('/api/v1/citas/disponibilidad/'.$martes->toDateString())->assertOk()->json('datos.horarios_libres');

        $this->assertNotContains('09:00', $libres);
        $this->assertNotContains('09:20', $libres);
        $this->assertContains('09:40', $libres);
        $this->getJson('/api/v1/citas/disponibilidad/'.$martes->copy()->addDay()->toDateString())->assertUnprocessable();
    }

    public function test_tracking_requires_folio_and_matching_phone(): void
    {
        $cita = $this->cita();
        $folio = 'CITA-'.str_pad((string) $cita->id, 6, '0', STR_PAD_LEFT);

        $this->postJson('/api/v1/seguimiento', ['folio' => $folio, 'celular' => '5587654321'])
            ->assertOk()
            ->assertJsonPath('datos.estatus', 'solicitada')
            ->assertDontSee('Prueba')
            ->assertDontSee('prueba@example.test');

        $celularEquivocado = $this->postJson('/api/v1/seguimiento', ['folio' => $folio, 'celular' => '5500000000'])->assertNotFound()->json();
        $folioInexistente = $this->postJson('/api/v1/seguimiento', ['folio' => 'CITA-999999', 'celular' => '5512345678'])->assertNotFound()->json();
        $this->assertSame($celularEquivocado, $folioInexistente);
    }
}
