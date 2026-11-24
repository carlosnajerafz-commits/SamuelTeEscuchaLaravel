<?php

namespace App\Services;

use App\Models\Apoyo;
use App\Models\EventoComedor;
use App\Models\Queja;
use Carbon\Carbon;

/**
 * Chatbot de atención ciudadana — motor de reglas.
 *
 * No usa inteligencia artificial ni servicios externos: clasifica el mensaje
 * por coincidencia de palabras clave y responde desde un guion fijo o desde
 * la base de datos del propio sistema.
 *
 * Decisión de diseño: al no enviar nada fuera del servidor, ningún dato que
 * el ciudadano escriba sale de la infraestructura de la oficina. Esto evita
 * la transferencia de datos personales a un tercero, que exigiría
 * consentimiento expreso conforme a la LFPDPPP.
 *
 * La conversación de seguimiento necesita dos turnos (folio y luego celular);
 * ese estado intermedio vive en la sesión, nunca en la base de datos.
 */
class ChatbotService
{
    private const TELEFONO = SeguimientoService::TELEFONO;

    private const WHATSAPP = 'https://wa.me/525521724723';

    /**
     * Intenciones reconocidas. El orden importa solo para desempatar:
     * gana la que acumule más coincidencias y, a igualdad, la primera.
     *
     * @var array<string, list<string>>
     */
    private const INTENCIONES = [
        'seguimiento' => [
            'folio', 'seguimiento', 'estatus', 'estado', 'avance', 'mi solicitud',
            'mi tramite', 'como va', 'que paso con', 'rastrear', 'consultar',
        ],
        'cita' => [
            'cita', 'citas', 'agendar', 'audiencia', 'ver al diputado',
            'hablar con el diputado', 'martes ciudadano', 'martes',
        ],
        'queja' => [
            'queja', 'quejas', 'reporte', 'reportar', 'denuncia', 'problema',
            'alumbrado', 'bache', 'baches', 'agua', 'basura', 'drenaje',
            'fuga', 'luminaria', 'seguridad', 'calle',
        ],
        'apoyos' => [
            'apoyo', 'apoyos', 'ayuda social', 'despensa', 'beca', 'programa',
            'programas', 'solicitar apoyo', 'apoyo social',
        ],
        'comedor' => [
            'comedor', 'comida', 'alimento', 'alimentos', 'desayuno',
            'comedor solidario', 'comer',
        ],
        'contacto' => [
            'telefono', 'numero', 'contacto', 'whatsapp', 'direccion',
            'donde estan', 'ubicacion', 'oficina', 'como los contacto', 'llamar',
        ],
        'horario' => [
            'horario', 'horarios', 'a que hora', 'que dias', 'cuando abren',
            'cuando atienden', 'abierto',
        ],
        'privacidad' => [
            'privacidad', 'datos personales', 'mis datos', 'aviso de privacidad',
            'arco', 'proteccion de datos', 'borrar mis datos',
        ],
        'saludo' => [
            'hola', 'buenas', 'buenos dias', 'buenas tardes', 'buenas noches',
            'que tal', 'saludos', 'holi',
        ],
        'despedida' => [
            'adios', 'bye', 'hasta luego', 'nos vemos', 'me voy', 'chao',
        ],
        'agradecimiento' => [
            'gracias', 'muchas gracias', 'te agradezco', 'muy amable',
        ],
        'ayuda' => [
            'ayuda', 'que puedes hacer', 'opciones', 'menu', 'que haces',
            'para que sirves',
        ],
    ];

    public function __construct(
        private readonly CitaService $citas,
        private readonly SeguimientoService $seguimiento,
    ) {}

    /**
     * Procesa un mensaje y devuelve la respuesta del bot.
     *
     * @param  string  $mensaje  Texto escrito por el ciudadano.
     * @param  array  $estado  Estado de la conversación (viene de la sesión).
     * @return array{respuesta: string, sugerencias: list<string>, estado: array}
     */
    public function responder(string $mensaje, array $estado = []): array
    {
        $texto = $this->normalizar($mensaje);

        if ($texto === '') {
            return $this->salida(
                'No alcancé a leer tu mensaje. ¿Me lo escribes de nuevo?',
                $this->sugerenciasIniciales()
            );
        }

        // Si quedó una conversación de seguimiento a medias, continúa ahí.
        if (($estado['esperando'] ?? null) !== null) {
            return $this->continuarSeguimiento($mensaje, $texto, $estado);
        }

        return match ($this->clasificar($texto)) {
            'seguimiento' => $this->iniciarSeguimiento($mensaje),
            'cita' => $this->responderCita(),
            'queja' => $this->responderQueja(),
            'apoyos' => $this->responderApoyos(),
            'comedor' => $this->responderComedor(),
            'contacto' => $this->responderContacto(),
            'horario' => $this->responderHorario(),
            'privacidad' => $this->responderPrivacidad(),
            'saludo' => $this->responderSaludo(),
            'despedida' => $this->responderDespedida(),
            'agradecimiento' => $this->responderAgradecimiento(),
            'ayuda' => $this->responderAyuda(),
            default => $this->responderNoEntendido(),
        };
    }

    /* ==========================================================
       CLASIFICACIÓN
    ========================================================== */

    /**
     * Quita acentos, signos y mayúsculas para comparar sin sorpresas.
     */
    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto), 'UTF-8');

        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);

        // Deja letras, números y espacios; colapsa espacios repetidos.
        $texto = preg_replace('/[^a-z0-9\s-]/u', ' ', $texto) ?? '';

        return trim(preg_replace('/\s+/', ' ', $texto) ?? '');
    }

    /**
     * Devuelve la intención con más coincidencias, o null si ninguna coincide.
     */
    private function clasificar(string $texto): ?string
    {
        $mejor = null;
        $mejorPuntaje = 0;

        foreach (self::INTENCIONES as $intencion => $claves) {
            $puntaje = 0;

            foreach ($claves as $clave) {
                if (str_contains($texto, $clave)) {
                    // Una frase de varias palabras pesa más que una suelta.
                    $puntaje += str_contains($clave, ' ') ? 3 : 1;
                }
            }

            if ($puntaje > $mejorPuntaje) {
                $mejor = $intencion;
                $mejorPuntaje = $puntaje;
            }
        }

        return $mejor;
    }

    /* ==========================================================
       SEGUIMIENTO DE FOLIO (dos turnos)
    ========================================================== */

    private function iniciarSeguimiento(string $mensajeOriginal): array
    {
        $folio = $this->seguimiento->extraerFolio($mensajeOriginal);

        if ($folio) {
            return $this->pedirCelular($folio['folio'], 'Perfecto, folio');
        }

        return $this->salida(
            "Con gusto reviso el avance de tu solicitud.\n\n"
            .'Escríbeme tu folio tal como aparece en el comprobante. '
            .'Por ejemplo: CITA-000014, QUEJA-000003, APOYO-000007 o COMEDOR-000002.',
            ['Cancelar consulta'],
            ['esperando' => 'folio']
        );
    }

    private function continuarSeguimiento(string $mensajeOriginal, string $texto, array $estado): array
    {
        if (str_contains($texto, 'cancelar') || str_contains($texto, 'salir')) {
            return $this->salida(
                'Listo, cancelé la consulta. ¿Te ayudo con algo más?',
                $this->sugerenciasIniciales()
            );
        }

        if ($estado['esperando'] === 'folio') {
            $folio = $this->seguimiento->extraerFolio($mensajeOriginal);

            if (! $folio) {
                return $this->salida(
                    "Ese folio no tiene el formato que esperaba.\n\n"
                    .'Debe empezar con CITA, QUEJA, APOYO o COMEDOR, seguido del número. '
                    .'Por ejemplo: CITA-000014.',
                    ['Cancelar consulta'],
                    ['esperando' => 'folio']
                );
            }

            return $this->pedirCelular($folio['folio'], 'Anotado:');
        }

        $celular = preg_replace('/\D/', '', $mensajeOriginal) ?? '';

        if (strlen($celular) !== 10) {
            return $this->salida(
                'Necesito el celular completo, a 10 dígitos. Por ejemplo: 5512345678.',
                ['Cancelar consulta'],
                ['esperando' => 'celular', 'folio' => $estado['folio'] ?? null]
            );
        }

        return $this->resolverSeguimiento((string) ($estado['folio'] ?? ''), $celular);
    }

    private function pedirCelular(string $folio, string $entrada): array
    {
        return $this->salida(
            "{$entrada} **{$folio}**.\n\n"
            .'Por seguridad necesito confirmar tu identidad: escríbeme los 10 dígitos '
            .'del celular que registraste en la solicitud.',
            ['Cancelar consulta'],
            ['esperando' => 'celular', 'folio' => $folio]
        );
    }

    private function resolverSeguimiento(string $folio, string $celular): array
    {
        $resultado = $this->seguimiento->consultar($folio, $celular);

        // Misma respuesta si el folio no existe o si el celular no coincide.
        if (! $resultado) {
            return $this->salida(
                "No encontré una solicitud con ese folio y ese celular.\n\n"
                .'Revisa que ambos datos sean los del comprobante. Si el problema sigue, '
                .'llámanos al '.self::TELEFONO.' y con gusto lo revisamos contigo.',
                ['Consultar otro folio', 'Ver teléfono de contacto']
            );
        }

        $fecha = $resultado['registrado_en'] ? Carbon::parse($resultado['registrado_en'])->format('d/m/Y') : null;

        return $this->salida(
            "**{$resultado['folio']}** · {$resultado['tipo']}\n\n"
            .'Estatus: **'.ucfirst($resultado['estatus'])."**\n"
            .($fecha ? "Registrada el {$fecha}\n\n" : "\n")
            .$resultado['descripcion'],
            ['Consultar otro folio', '¿Qué más puedes hacer?']
        );
    }

    /* ==========================================================
       RESPUESTAS POR INTENCIÓN
    ========================================================== */

    private function responderCita(): array
    {
        $proximoMartes = Carbon::now()->next(Carbon::TUESDAY);

        $libres = count($this->citas->available($proximoMartes->toDateString()));

        $disponibilidad = $libres > 0
            ? "Para el martes {$proximoMartes->translatedFormat('j \d\e F')} quedan **{$libres} horarios disponibles**."
            : "El martes {$proximoMartes->translatedFormat('j \d\e F')} ya está lleno, pero puedes elegir un martes posterior.";

        return $this->salida(
            "**Martes ciudadano**\n\n"
            ."El diputado atiende personalmente los martes, de 9:00 a 15:00 horas, en turnos de 20 minutos.\n\n"
            ."{$disponibilidad}\n\n"
            .'Para agendar entra a la sección **Agendar cita**, elige un martes en el calendario, '
            ."selecciona el horario y llena tus datos. Al terminar recibes un folio para dar seguimiento.\n\n"
            .'Ten en cuenta que no es posible agendar para el mismo día: la cita debe ser para un martes posterior.',
            ['Consultar mi folio', 'Ver teléfono de contacto']
        );
    }

    private function responderQueja(): array
    {
        $tipos = Queja::query()
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->orderByDesc('total')
            ->limit(4)
            ->pluck('tipo')
            ->filter()
            ->implode(', ');

        $frecuentes = $tipos !== ''
            ? "\n\nLos reportes más frecuentes en el distrito son: {$tipos}."
            : '';

        return $this->salida(
            "**Reportar una problemática**\n\n"
            .'Puedes reportar fallas de alumbrado público, abasto de agua, recolección de basura, '
            ."estado de las calles, áreas verdes y seguridad pública.\n\n"
            .'Entra a **Solicitud de Gestión**, llena el formulario y adjunta una fotografía de la '
            ."situación. La evidencia ayuda a que la dependencia la atienda más rápido.\n\n"
            .'Al enviarlo recibes un folio con el que puedes consultar el avance aquí mismo.'
            .$frecuentes,
            ['Consultar mi folio', '¿Qué más puedes hacer?']
        );
    }

    private function responderApoyos(): array
    {
        $apoyos = Apoyo::where('activo', true)
            ->orderByDesc('created_at')
            ->get(['nombre', 'descripcion']);

        if ($apoyos->isEmpty()) {
            return $this->salida(
                "En este momento no hay apoyos abiertos a registro.\n\n"
                .'Te sugiero revisar la sección de apoyos en unos días, o llamar al '
                .self::TELEFONO.' para preguntar por las próximas convocatorias.',
                ['Ver teléfono de contacto', '¿Qué más puedes hacer?']
            );
        }

        $lista = $apoyos
            ->map(function ($a) {
                $linea = '• **'.$a->nombre.'**';

                return $a->descripcion ? $linea.': '.$a->descripcion : $linea;
            })
            ->implode("\n");

        return $this->salida(
            "**Apoyos disponibles ahora mismo**\n\n"
            .$lista."\n\n"
            .'Para registrarte entra a la sección **Pre-registro para Apoyos Solidarios**, '
            .'elige el apoyo y llena tus datos. Recibirás un folio de seguimiento.',
            ['Consultar mi folio', '¿Qué más puedes hacer?']
        );
    }

    private function responderComedor(): array
    {
        $eventos = EventoComedor::query()
            ->where('activo', true)
            ->whereDate('fecha', '>=', now()->toDateString())
            ->withSum(
                ['registros as personas_registradas' => fn ($q) => $q->where('estatus', '!=', 'cancelado')],
                'numero_personas'
            )
            ->orderBy('fecha')
            ->limit(3)
            ->get();

        if ($eventos->isEmpty()) {
            return $this->salida(
                "No hay fechas de comedor solidario programadas por ahora.\n\n"
                .'Las nuevas fechas se publican en la sección de comedor. También puedes llamar al '
                .self::TELEFONO.' para preguntar.',
                ['Ver teléfono de contacto', '¿Qué más puedes hacer?']
            );
        }

        $lista = $eventos->map(function ($e) {
            $fecha = Carbon::parse($e->fecha)->translatedFormat('l j \d\e F');
            $hora = substr((string) $e->hora_inicio, 0, 5).' a '.substr((string) $e->hora_fin, 0, 5);
            $linea = '• **'.ucfirst($fecha)."**, de {$hora}";

            if ($e->lugar) {
                $linea .= "\n  Lugar: {$e->lugar}";
            }

            $cupo = (int) $e->cupo_maximo;
            if ($cupo > 0) {
                $disponible = max(0, $cupo - (int) ($e->personas_registradas ?? 0));
                $linea .= "\n  Lugares disponibles: {$disponible}";
            }

            return $linea;
        })->implode("\n\n");

        return $this->salida(
            "**Próximas fechas del comedor solidario**\n\n"
            .$lista."\n\n"
            .'Para apartar tu lugar entra a la sección **Comedor Solidario** y llena el registro '
            .'indicando cuántas personas asistirán.',
            ['Consultar mi folio', '¿Qué más puedes hacer?']
        );
    }

    private function responderContacto(): array
    {
        return $this->salida(
            "**Cómo contactarnos**\n\n"
            .'Teléfono y WhatsApp: **'.self::TELEFONO."**\n"
            .'WhatsApp directo: '.self::WHATSAPP."\n\n"
            .'También puedes escribirnos por Facebook o Instagram, o acudir a la oficina de '
            .'atención ciudadana del Distrito XXXIII. El mapa con la ubicación está en el '
            .'ícono de localización, arriba a la derecha del sitio.',
            ['Agendar una cita', '¿Qué más puedes hacer?']
        );
    }

    private function responderHorario(): array
    {
        return $this->salida(
            "**Horarios de atención**\n\n"
            ."• Atención ciudadana en oficina: lunes a viernes, 9:00 a 17:00 horas.\n"
            ."• Citas con el diputado: únicamente los martes, 9:00 a 15:00 horas.\n"
            ."• Este sitio: disponible las 24 horas para registrar solicitudes.\n\n"
            .'Las fechas del comedor solidario varían; pregúntame por el comedor y te digo las próximas.',
            ['Agendar una cita', 'Ver fechas del comedor']
        );
    }

    private function responderPrivacidad(): array
    {
        return $this->salida(
            "**Protección de tus datos**\n\n"
            .'Los datos que registras se usan únicamente para atender tu solicitud y canalizarla '
            ."a la dependencia que corresponda. No se venden ni se comparten con fines comerciales.\n\n"
            .'Tienes derecho a acceder, rectificar, cancelar u oponerte al tratamiento de tus datos '
            .'(derechos ARCO). Para ejercerlos llama al '.self::TELEFONO.".\n\n"
            .'El aviso de privacidad completo está disponible en la página de inicio.',
            ['Ver teléfono de contacto', '¿Qué más puedes hacer?']
        );
    }

    private function responderSaludo(): array
    {
        $hora = (int) now()->format('H');
        $saludo = match (true) {
            $hora < 12 => 'Buenos días',
            $hora < 19 => 'Buenas tardes',
            default => 'Buenas noches',
        };

        return $this->salida(
            "{$saludo}. Soy el asistente de la oficina de atención ciudadana del Distrito XXXIII.\n\n"
            .'¿En qué te puedo ayudar?',
            $this->sugerenciasIniciales()
        );
    }

    private function responderDespedida(): array
    {
        return $this->salida(
            'Que tengas buen día. Aquí estaré cuando necesites algo más.',
            []
        );
    }

    private function responderAgradecimiento(): array
    {
        return $this->salida(
            'Con gusto. ¿Necesitas algo más?',
            $this->sugerenciasIniciales()
        );
    }

    private function responderAyuda(): array
    {
        return $this->salida(
            "Puedo ayudarte con lo siguiente:\n\n"
            ."• **Consultar tu folio** y decirte cómo va tu solicitud\n"
            ."• Explicarte cómo **agendar una cita** con el diputado\n"
            ."• Decirte cómo **reportar un problema** de servicios públicos\n"
            ."• Mostrarte qué **apoyos** están abiertos a registro\n"
            ."• Darte las próximas fechas del **comedor solidario**\n"
            ."• Compartirte los **datos de contacto** y los horarios\n\n"
            .'Escríbeme lo que necesites con tus propias palabras.',
            $this->sugerenciasIniciales()
        );
    }

    private function responderNoEntendido(): array
    {
        return $this->salida(
            "No estoy seguro de haber entendido.\n\n"
            .'Puedo ayudarte con citas, reportes de servicios públicos, apoyos, comedor solidario '
            .'y consulta de folios. Si necesitas algo distinto, llama al '.self::TELEFONO
            .' y te atenderá una persona del equipo.',
            $this->sugerenciasIniciales()
        );
    }

    /* ==========================================================
       UTILIDADES
    ========================================================== */

    /** @return list<string> */
    private function sugerenciasIniciales(): array
    {
        return [
            'Consultar mi folio',
            'Agendar una cita',
            'Reportar un problema',
            'Ver apoyos disponibles',
        ];
    }

    /**
     * @param  list<string>  $sugerencias
     * @return array{respuesta: string, sugerencias: list<string>, estado: array}
     */
    private function salida(string $respuesta, array $sugerencias = [], array $estado = []): array
    {
        return [
            'respuesta' => $respuesta,
            'sugerencias' => $sugerencias,
            'estado' => $estado,
        ];
    }
}
