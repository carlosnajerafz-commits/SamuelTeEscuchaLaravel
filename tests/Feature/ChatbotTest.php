<?php

namespace Tests\Feature;

use App\Models\Apoyo;
use App\Models\EventoComedor;
use App\Models\Queja;
use App\Models\RegistroComedor;
use Tests\LegacyDatabaseTestCase;

class ChatbotTest extends LegacyDatabaseTestCase
{
    private function queja(): Queja
    {
        return Queja::create(['nombre' => 'Prueba', 'apellido_paterno' => 'Ciudadano', 'apellido_materno' => 'Local', 'celular_1' => '5512345678', 'correo' => 'prueba@example.test', 'calle' => 'Principal', 'no_exterior' => '10', 'colonia' => 'Centro', 'municipio' => 'Tecámac', 'codigo_postal' => '55740', 'tipo' => 'Alumbrado', 'descripcion' => 'Luminaria apagada', 'estatus' => 'atendida']);
    }

    public function test_public_layout_renders_the_widget(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('stec-chat', false)->assertSee(route('chatbot.mensaje'), false);
    }

    public function test_message_is_required(): void
    {
        $this->postJson(route('chatbot.mensaje'), ['mensaje' => ''])->assertUnprocessable();
    }

    public function test_lists_active_supports_from_the_database(): void
    {
        Apoyo::create(['nombre' => 'Despensa invernal', 'activo' => true]);
        Apoyo::create(['nombre' => 'Programa cerrado', 'activo' => false]);

        $this->postJson(route('chatbot.mensaje'), ['mensaje' => '¿Qué apoyos hay?'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertSee('Despensa invernal')
            ->assertDontSee('Programa cerrado');
    }

    public function test_comedor_shows_remaining_capacity(): void
    {
        $evento = EventoComedor::create(['fecha' => today()->addDay()->toDateString(), 'hora_inicio' => '12:00:00', 'hora_fin' => '15:00:00', 'lugar' => 'Centro', 'cupo_maximo' => 10, 'activo' => true]);
        RegistroComedor::create(['evento_id' => $evento->id, 'nombre' => 'A', 'apellido_paterno' => 'B', 'apellido_materno' => 'C', 'celular_1' => '5512345678', 'numero_personas' => 3, 'estatus' => 'pendiente']);
        RegistroComedor::create(['evento_id' => $evento->id, 'nombre' => 'A', 'apellido_paterno' => 'B', 'apellido_materno' => 'C', 'celular_1' => '5512345678', 'numero_personas' => 4, 'estatus' => 'cancelado']);

        $this->postJson(route('chatbot.mensaje'), ['mensaje' => 'comedor'])
            ->assertOk()
            ->assertSee('Lugares disponibles: 7');
    }

    public function test_folio_lookup_requires_matching_phone_across_two_turns(): void
    {
        $queja = $this->queja();
        $folio = 'QUEJA-'.str_pad((string) $queja->id, 6, '0', STR_PAD_LEFT);

        $this->postJson(route('chatbot.mensaje'), ['mensaje' => 'Quiero consultar mi folio'])->assertOk();
        $this->postJson(route('chatbot.mensaje'), ['mensaje' => $folio])->assertOk()->assertSee($folio);
        $this->postJson(route('chatbot.mensaje'), ['mensaje' => '55 1234 5678'])
            ->assertOk()
            ->assertSee('Atendida')
            ->assertDontSee('Prueba');
    }

    public function test_wrong_phone_gets_the_same_answer_as_an_unknown_folio(): void
    {
        $queja = $this->queja();

        $this->postJson(route('chatbot.mensaje'), ['mensaje' => 'estatus de QUEJA-'.$queja->id])->assertOk();
        $wrongPhone = $this->postJson(route('chatbot.mensaje'), ['mensaje' => '5500000000'])->json('respuesta');

        $this->postJson(route('chatbot.mensaje'), ['mensaje' => 'estatus de QUEJA-999'])->assertOk();
        $unknownFolio = $this->postJson(route('chatbot.mensaje'), ['mensaje' => '5512345678'])->json('respuesta');

        $this->assertSame($wrongPhone, $unknownFolio);
        $this->assertStringContainsString('No encontré', $wrongPhone);
    }

    public function test_reset_clears_the_pending_conversation(): void
    {
        $this->postJson(route('chatbot.mensaje'), ['mensaje' => 'folio'])->assertOk();
        $this->postJson(route('chatbot.reiniciar'))->assertOk();

        $this->postJson(route('chatbot.mensaje'), ['mensaje' => 'hola'])->assertOk()->assertSee('asistente');
    }

    public function test_cita_answer_counts_real_availability(): void
    {
        $this->postJson(route('chatbot.mensaje'), ['mensaje' => 'quiero agendar una cita'])
            ->assertOk()
            ->assertSee('18 horarios disponibles');
    }
}
