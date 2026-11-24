<?php

namespace Tests\Feature;

use App\Models\Empleado;
use Tests\LegacyDatabaseTestCase;

class EmployeeAuthenticationTest extends LegacyDatabaseTestCase
{
    public function test_guest_cannot_access_employee_panel(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Usuario');
    }

    public function test_legacy_login_ignores_username_case_and_spaces_and_keeps_existing_hash(): void
    {
        $employee = Empleado::factory()->create(['usuario' => ' STEpersonal ']);
        $hash = $employee->password_hash;
        $this->post('/login', ['usuario' => ' stepersonal ', 'password' => 'prueba-segura'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($employee);
        $this->assertSame($hash, $employee->fresh()->password_hash);
        $this->get('/dashboard')->assertOk()->assertSee($employee->nombre_completo);
        $this->get('/login')->assertRedirect('/dashboard');
    }

    public function test_bad_password_does_not_authenticate_or_flash_password(): void
    {
        Empleado::factory()->create(['usuario' => 'personal']);
        $this->from('/login')->post('/login', ['usuario' => 'personal', 'password' => 'incorrecta'])->assertRedirect('/login')->assertSessionHasErrors('usuario')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_login_is_limited_by_ip(): void
    {
        $employee = Empleado::factory()->create(['usuario' => 'personal']);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['usuario' => 'personal', 'password' => 'incorrecta'])->assertSessionHasErrors('usuario');
        }
        $this->post('/login', ['usuario' => 'personal', 'password' => 'prueba-segura'])->assertSessionHasErrors('usuario');
        $this->assertGuest();
        $this->travel(61)->seconds();
        $this->post('/login', ['usuario' => 'personal', 'password' => 'prueba-segura'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($employee);
    }

    public function test_logout_invalidates_session_and_requires_post(): void
    {
        $employee = Empleado::factory()->create();
        $this->actingAs($employee)->get('/logout')->assertStatus(405);
        $this->withSession(['private-marker' => 'sensitive'])->post('/logout')->assertRedirect('/login')->assertSessionMissing('private-marker');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_password_hash_is_hidden_from_serialization(): void
    {
        $this->assertArrayNotHasKey('password_hash', Empleado::factory()->make()->toArray());
    }
}
