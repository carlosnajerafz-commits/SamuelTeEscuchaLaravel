<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Punto de entrada del chatbot ciudadano.
 *
 * El widget del sitio manda aquí cada mensaje por POST y recibe la
 * respuesta en JSON. El estado de una conversación a medias (por ejemplo,
 * cuando se está pidiendo el folio y luego el celular) vive en la sesión
 * del navegador, no en la base de datos.
 */
class ChatbotController extends Controller
{
    private const CLAVE_SESION = 'chatbot_estado';

    public function __construct(private readonly ChatbotService $chatbot) {}

    /**
     * POST /chatbot/mensaje
     */
    public function mensaje(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'mensaje' => ['required', 'string', 'max:500'],
        ], [
            'mensaje.required' => 'Escribe un mensaje.',
            'mensaje.max' => 'El mensaje es demasiado largo (máximo 500 caracteres).',
        ]);

        $estadoPrevio = $request->session()->get(self::CLAVE_SESION, []);

        $resultado = $this->chatbot->responder($datos['mensaje'], $estadoPrevio);

        // Guardamos el estado solo si la conversación quedó a medias.
        if (! empty($resultado['estado']['esperando'])) {
            $request->session()->put(self::CLAVE_SESION, $resultado['estado']);
        } else {
            $request->session()->forget(self::CLAVE_SESION);
        }

        return response()->json([
            'ok' => true,
            'respuesta' => $resultado['respuesta'],
            'sugerencias' => $resultado['sugerencias'],
        ]);
    }

    /**
     * POST /chatbot/reiniciar
     *
     * Borra el estado de la conversación.
     */
    public function reiniciar(Request $request): JsonResponse
    {
        $request->session()->forget(self::CLAVE_SESION);

        return response()->json(['ok' => true]);
    }
}
