<?php

namespace Tests\Feature;

use App\Models\Apoyo;
use App\Models\Cita;
use App\Models\Empleado;
use App\Models\EventoComedor;
use App\Models\Queja;
use App\Models\RegistroComedor;
use Illuminate\Support\Facades\Hash;
use Tests\LegacyDatabaseTestCase;

class AdminPanelTest extends LegacyDatabaseTestCase
{
    private function person(array $overrides = []): array
    {
        return [...['nombre' => 'Ana', 'apellido_paterno' => 'López', 'apellido_materno' => 'Ruiz', 'celular_1' => '5511112222', 'celular_2' => '', 'correo' => 'ana@example.test', 'calle' => 'Principal', 'no_exterior' => '1', 'colonia' => 'Centro', 'municipio' => 'Tecámac', 'codigo_postal' => '55740'], ...$overrides];
    }

    private function complaint(array $overrides = []): Queja
    {
        return Queja::create($this->person(['tipo' => 'Bacheo', 'descripcion' => 'Bache en la calle', 'estatus' => 'pendiente', ...$overrides]));
    }

    private function event(array $overrides = []): EventoComedor
    {
        return EventoComedor::create(['fecha' => today()->addDays(2)->toDateString(), 'hora_inicio' => '12:00:00', 'hora_fin' => '15:00:00', 'lugar' => 'Plaza central', 'cupo_maximo' => 10, 'activo' => true, ...$overrides]);
    }

    public function test_guests_are_redirected_from_every_new_admin_route(): void
    {
        $this->get('/admin/empleados')->assertRedirect('/login');
        $this->get('/admin/empleados/create')->assertRedirect('/login');
        $this->post('/admin/empleados', [])->assertRedirect('/login');
        $this->put('/admin/empleados/1', [])->assertRedirect('/login');
        $this->delete('/admin/empleados/1')->assertRedirect('/login');
        $this->delete('/admin/catalogos/apoyos/1')->assertRedirect('/login');
        $this->get('/admin/solicitudes/quejas/exportar')->assertRedirect('/login');
    }

    public function test_dashboard_summarizes_pending_requests_and_upcoming_agenda(): void
    {
        $this->complaint();
        $this->complaint();
        $this->complaint(['estatus' => 'atendida']);
        $tuesday = today()->next('Tuesday')->toDateString();
        Cita::create($this->person(['nombre' => 'Próxima', 'fecha' => $tuesday, 'hora' => '09:20:00', 'estatus' => 'aceptada']));
        Cita::create($this->person(['nombre' => 'Cancelada', 'fecha' => $tuesday, 'hora' => '09:40:00', 'estatus' => 'cancelada']));
        Cita::create($this->person(['nombre' => 'Pasada', 'fecha' => today()->subWeek()->toDateString(), 'hora' => '09:00:00', 'estatus' => 'solicitada']));
        $event = $this->event();
        RegistroComedor::create($this->person(['evento_id' => $event->id, 'numero_personas' => 3, 'estatus' => 'pendiente']));
        RegistroComedor::create($this->person(['evento_id' => $event->id, 'numero_personas' => 4, 'estatus' => 'cancelado']));
        $this->event(['lugar' => 'Evento inactivo', 'activo' => false]);

        $response = $this->actingAs(Empleado::factory()->create())->get('/dashboard')->assertOk();

        $response->assertViewHas('summaries', fn ($summaries) => $summaries['quejas']['pending'] === 2 && $summaries['quejas']['total'] === 3 && $summaries['citas']['pending'] === 1);
        $response->assertSee('Próxima')->assertDontSee('Cancelada')->assertDontSee('Pasada');
        $response->assertSee('Plaza central')->assertSee('3 / 10')->assertDontSee('Evento inactivo');
    }

    public function test_menu_links_every_admin_section(): void
    {
        $response = $this->actingAs(Empleado::factory()->create())->get('/dashboard')->assertOk();

        foreach (['/admin/solicitudes/quejas', '/admin/solicitudes/citas', '/admin/catalogos/apoyos', '/admin/catalogos/comedor', '/admin/bloqueos', '/admin/codigos-postales', '/admin/empleados'] as $path) {
            $response->assertSee(url($path), false);
        }
    }

    public function test_employee_can_register_a_colleague_who_can_then_log_in(): void
    {
        $this->actingAs(Empleado::factory()->create());
        $this->get('/admin/empleados/create')->assertOk();
        $this->post('/admin/empleados', ['nombre_completo' => ' María Pérez ', 'usuario' => ' mperez ', 'password' => 'clave-segura-1', 'password_confirmation' => 'clave-segura-1'])->assertRedirect('/admin/empleados')->assertSessionHasNoErrors();

        $employee = Empleado::where('usuario', 'mperez')->firstOrFail();
        $this->assertSame('María Pérez', $employee->nombre_completo);
        $this->assertTrue(password_verify('clave-segura-1', $employee->password_hash));
        $this->get('/admin/empleados')->assertSee('María Pérez');

        $this->post('/logout');
        $this->post('/login', ['usuario' => 'MPEREZ', 'password' => 'clave-segura-1'])->assertRedirect('/dashboard');
    }

    public function test_employee_data_is_validated(): void
    {
        $this->actingAs(Empleado::factory()->create(['usuario' => 'existente']));

        $this->post('/admin/empleados', ['nombre_completo' => 'Otro', 'usuario' => 'EXISTENTE', 'password' => 'clave-segura-1', 'password_confirmation' => 'clave-segura-1'])->assertSessionHasErrors('usuario');
        $this->post('/admin/empleados', ['nombre_completo' => 'Otro', 'usuario' => 'con espacio', 'password' => 'clave-segura-1', 'password_confirmation' => 'clave-segura-1'])->assertSessionHasErrors('usuario');
        $this->post('/admin/empleados', ['nombre_completo' => '', 'usuario' => 'nuevo', 'password' => 'corta', 'password_confirmation' => 'distinta'])->assertSessionHasErrors(['nombre_completo', 'password']);
        $this->post('/admin/empleados', ['nombre_completo' => 'Otro', 'usuario' => 'nuevo'])->assertSessionHasErrors('password');
        $this->assertDatabaseCount('empleados', 1);
    }

    public function test_updating_without_password_keeps_it_and_changing_it_revokes_api_tokens(): void
    {
        $this->actingAs(Empleado::factory()->create());
        $colleague = Empleado::factory()->create(['usuario' => 'colega']);
        $originalHash = $colleague->password_hash;
        $colleague->createToken('app');

        $this->get('/admin/empleados/'.$colleague->id.'/edit')->assertOk()->assertSee('colega');
        $this->put('/admin/empleados/'.$colleague->id, ['nombre_completo' => 'Nombre nuevo', 'usuario' => 'colega'])->assertSessionHasNoErrors();
        $this->assertSame($originalHash, $colleague->fresh()->password_hash);
        $this->assertSame('Nombre nuevo', $colleague->fresh()->nombre_completo);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->put('/admin/empleados/'.$colleague->id, ['nombre_completo' => 'Nombre nuevo', 'usuario' => 'colega', 'password' => 'otra-clave-99', 'password_confirmation' => 'otra-clave-99'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('otra-clave-99', $colleague->fresh()->password_hash));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_employee_cannot_delete_own_account_but_can_delete_others(): void
    {
        $employee = Empleado::factory()->create();
        $colleague = Empleado::factory()->create();
        $this->actingAs($employee);

        $this->delete('/admin/empleados/'.$employee->id)->assertSessionHasErrors('empleado');
        $this->assertModelExists($employee);

        $this->delete('/admin/empleados/'.$colleague->id)->assertRedirect('/admin/empleados');
        $this->assertModelMissing($colleague);
        $this->delete('/admin/empleados/9999')->assertNotFound();
    }

    public function test_catalog_items_can_be_deleted_unless_an_event_has_registrations(): void
    {
        $this->actingAs(Empleado::factory()->create());
        $support = Apoyo::create(['nombre' => 'Despensa', 'activo' => true]);
        $this->delete('/admin/catalogos/apoyos/'.$support->id)->assertRedirect('/admin/catalogos/apoyos');
        $this->assertModelMissing($support);

        $busyEvent = $this->event();
        RegistroComedor::create($this->person(['evento_id' => $busyEvent->id, 'numero_personas' => 2, 'estatus' => 'pendiente']));
        $this->get('/admin/catalogos/comedor')->assertOk()->assertSee('2 / 10');
        $this->delete('/admin/catalogos/comedor/'.$busyEvent->id)->assertSessionHasErrors('evento');
        $this->assertModelExists($busyEvent);

        $emptyEvent = $this->event();
        $this->delete('/admin/catalogos/comedor/'.$emptyEvent->id)->assertRedirect('/admin/catalogos/comedor');
        $this->assertModelMissing($emptyEvent);
        $this->delete('/admin/catalogos/otros/1')->assertNotFound();
    }

    public function test_requests_can_be_filtered_by_status_and_searched(): void
    {
        $this->complaint(['nombre' => 'Pendiente']);
        $this->complaint(['nombre' => 'Atendida', 'estatus' => 'atendida']);
        $this->complaint(['nombre' => 'Celular', 'celular_1' => '5599998888', 'estatus' => 'atendida']);
        $this->actingAs(Empleado::factory()->create());

        $this->get('/admin/solicitudes/quejas?estatus=pendiente')->assertOk()->assertSee('Pendiente')->assertDontSee('Atendida López');
        $this->get('/admin/solicitudes/quejas?q=Atendida')->assertOk()->assertSee('Atendida López')->assertDontSee('Pendiente López');
        $this->get('/admin/solicitudes/quejas?q=5599998888')->assertOk()->assertSee('Celular López')->assertDontSee('Pendiente López');
        $this->get('/admin/solicitudes/quejas?estatus=inventado')->assertSessionHasErrors('estatus');
    }

    public function test_employee_updates_status_and_deletes_a_request(): void
    {
        $complaint = $this->complaint();
        $this->actingAs(Empleado::factory()->create());

        $this->get('/admin/solicitudes/quejas/'.$complaint->id)->assertOk()->assertSee('Bache en la calle');
        $this->patch('/admin/solicitudes/quejas/'.$complaint->id, ['estatus' => 'atendida'])->assertSessionHasNoErrors();
        $this->assertSame('atendida', $complaint->fresh()->estatus);

        $this->delete('/admin/solicitudes/quejas/'.$complaint->id)->assertRedirect('/admin/solicitudes/quejas');
        $this->assertModelMissing($complaint);
        $this->get('/admin/solicitudes/quejas/'.$complaint->id)->assertNotFound();
    }

    public function test_export_escapes_spreadsheet_formulas_and_hides_document_paths(): void
    {
        $this->complaint(['nombre' => '=HYPERLINK("http://malicioso")', 'evidencia_path' => 'evidencias/secreto.jpg']);
        $this->complaint(['nombre' => 'Filtrada', 'estatus' => 'cerrada']);

        $csv = $this->actingAs(Empleado::factory()->create())->get('/admin/solicitudes/quejas/exportar?estatus=pendiente')->assertOk()->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBFid,", $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString('evidencia_path', $csv);
        $this->assertStringNotContainsString('secreto.jpg', $csv);
        $this->assertStringNotContainsString('Filtrada', $csv);
    }
}
