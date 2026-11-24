/**
 * Chatbot ciudadano — Samuel Te Escucha
 *
 * Habla únicamente con el propio servidor (POST /chatbot/mensaje).
 * No carga librerías externas ni manda nada a terceros.
 */
(function () {
  'use strict';

  var raiz = document.getElementById('stec-chat');
  if (!raiz) return;

  var urlMensaje   = raiz.dataset.urlMensaje;
  var urlReiniciar = raiz.dataset.urlReiniciar;
  var csrf         = raiz.dataset.csrf;

  var toggle      = document.getElementById('stec-chat-toggle');
  var panel       = document.getElementById('stec-chat-panel');
  var btnCerrar   = document.getElementById('stec-chat-cerrar');
  var contenedor  = document.getElementById('stec-chat-mensajes');
  var sugerencias = document.getElementById('stec-chat-sugerencias');
  var form        = document.getElementById('stec-chat-form');
  var input       = document.getElementById('stec-chat-input');
  var btnEnviar   = form.querySelector('.stec-chat__enviar');

  var yaSaludo = false;
  var enviando = false;

  /* ============================================================
     Abrir y cerrar
  ============================================================ */

  function abrir() {
    panel.hidden = false;
    raiz.classList.add('stec-chat--abierto');
    toggle.setAttribute('aria-expanded', 'true');
    toggle.setAttribute('aria-label', 'Cerrar asistente de atención ciudadana');

    if (!yaSaludo) {
      yaSaludo = true;
      enviar('hola', { silencioso: true });
    }

    input.focus();
  }

  function cerrar() {
    panel.hidden = true;
    raiz.classList.remove('stec-chat--abierto');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Abrir asistente de atención ciudadana');
    toggle.focus();
  }

  toggle.addEventListener('click', function () {
    panel.hidden ? abrir() : cerrar();
  });

  btnCerrar.addEventListener('click', cerrar);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !panel.hidden) cerrar();
  });

  /* ============================================================
     Pintar mensajes
  ============================================================ */

  /**
   * Convierte el texto del bot en HTML seguro.
   * Escapa todo y después reconvierte solo **negritas** y los saltos de línea.
   */
  function aHtmlSeguro(texto) {
    var escapado = texto
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    return escapado.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
  }

  function agregarBurbuja(texto, quien) {
    var burbuja = document.createElement('div');
    burbuja.className = 'stec-chat__burbuja stec-chat__burbuja--' + quien;

    if (quien === 'bot') {
      burbuja.innerHTML = aHtmlSeguro(texto);
    } else {
      burbuja.textContent = texto; // lo que escribe la persona nunca se interpreta
    }

    contenedor.appendChild(burbuja);
    contenedor.scrollTop = contenedor.scrollHeight;
  }

  function mostrarEscribiendo() {
    var bloque = document.createElement('div');
    bloque.className = 'stec-chat__escribiendo';
    bloque.id = 'stec-chat-escribiendo';
    bloque.innerHTML =
      '<span class="stec-chat__punto"></span>' +
      '<span class="stec-chat__punto"></span>' +
      '<span class="stec-chat__punto"></span>';
    contenedor.appendChild(bloque);
    contenedor.scrollTop = contenedor.scrollHeight;
  }

  function quitarEscribiendo() {
    var bloque = document.getElementById('stec-chat-escribiendo');
    if (bloque) bloque.remove();
  }

  function pintarSugerencias(lista) {
    sugerencias.innerHTML = '';
    if (!lista || !lista.length) return;

    lista.forEach(function (texto) {
      var boton = document.createElement('button');
      boton.type = 'button';
      boton.className = 'stec-chat__sugerencia';
      boton.textContent = texto;
      boton.addEventListener('click', function () {
        enviar(texto);
      });
      sugerencias.appendChild(boton);
    });
  }

  /* ============================================================
     Enviar al servidor
  ============================================================ */

  function enviar(texto, opciones) {
    opciones = opciones || {};
    if (enviando) return;

    texto = (texto || '').trim();
    if (!texto) return;

    enviando = true;
    btnEnviar.disabled = true;

    if (!opciones.silencioso) {
      agregarBurbuja(texto, 'usuario');
    }

    sugerencias.innerHTML = '';
    mostrarEscribiendo();

    fetch(urlMensaje, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify({ mensaje: texto })
    })
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function (datos) {
        quitarEscribiendo();
        agregarBurbuja(datos.respuesta, 'bot');
        pintarSugerencias(datos.sugerencias);
      })
      .catch(function () {
        quitarEscribiendo();
        agregarBurbuja(
          'No pude conectarme en este momento. Intenta de nuevo en unos segundos ' +
          'o llama al 55 2172 4723.',
          'bot'
        );
      })
      .finally(function () {
        enviando = false;
        btnEnviar.disabled = false;
        input.focus();
      });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var texto = input.value;
    input.value = '';
    enviar(texto);
  });

  /* ============================================================
     Limpieza al cerrar la pestaña
  ============================================================ */

  window.addEventListener('pagehide', function () {
    if (!yaSaludo || !navigator.sendBeacon) return;

    var datos = new FormData();
    datos.append('_token', csrf);
    navigator.sendBeacon(urlReiniciar, datos);
  });
})();
