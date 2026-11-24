{{--
    Widget del chatbot ciudadano.

    Uso: incluir  <x-chatbot />  justo antes de </body> en el layout público.

    Requiere:
      - public/assets/css/chatbot.css
      - public/assets/js/chatbot.js
      - Las rutas chatbot.mensaje y chatbot.reiniciar en routes/web.php
--}}

<div
    id="stec-chat"
    class="stec-chat"
    data-url-mensaje="{{ route('chatbot.mensaje') }}"
    data-url-reiniciar="{{ route('chatbot.reiniciar') }}"
    data-csrf="{{ csrf_token() }}"
>
    {{-- Botón flotante --}}
    <button
        type="button"
        id="stec-chat-toggle"
        class="stec-chat__toggle"
        aria-expanded="false"
        aria-controls="stec-chat-panel"
        aria-label="Abrir asistente de atención ciudadana"
    >
        <svg class="stec-chat__icono-abrir" width="26" height="26" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
        </svg>
        <svg class="stec-chat__icono-cerrar" width="26" height="26" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
    </button>

    {{-- Panel de conversación --}}
    <section
        id="stec-chat-panel"
        class="stec-chat__panel"
        role="dialog"
        aria-modal="false"
        aria-labelledby="stec-chat-titulo"
        hidden
    >
        <header class="stec-chat__encabezado">
            <div>
                <h2 id="stec-chat-titulo" class="stec-chat__titulo">Asistente ciudadano</h2>
                <p class="stec-chat__subtitulo">Distrito XXXIII</p>
            </div>
            <button
                type="button"
                id="stec-chat-cerrar"
                class="stec-chat__cerrar"
                aria-label="Cerrar asistente"
            >&times;</button>
        </header>

        {{-- Historial. aria-live para que un lector de pantalla anuncie las respuestas --}}
        <div
            id="stec-chat-mensajes"
            class="stec-chat__mensajes"
            role="log"
            aria-live="polite"
            aria-atomic="false"
        ></div>

        <div id="stec-chat-sugerencias" class="stec-chat__sugerencias"></div>

        <form id="stec-chat-form" class="stec-chat__form" autocomplete="off">
            <label for="stec-chat-input" class="stec-chat__etiqueta">
                Escribe tu mensaje
            </label>
            <input
                type="text"
                id="stec-chat-input"
                name="mensaje"
                class="stec-chat__input"
                placeholder="Escribe tu pregunta..."
                maxlength="500"
                required
            >
            <button type="submit" class="stec-chat__enviar" aria-label="Enviar mensaje">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>

        <p class="stec-chat__aviso">
            Asistente automático. Para casos urgentes llama al 55 2172 4723.
        </p>
    </section>
</div>

<link rel="stylesheet" href="{{ asset('assets/css/chatbot.css') }}">
<script src="{{ asset('assets/js/chatbot.js') }}" defer></script>
