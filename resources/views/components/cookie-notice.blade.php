{{--
    Aviso de cookies. El sitio solo usa cookies técnicas indispensables, así que el aviso
    es informativo. Se recuerda que ya se leyó con localStorage, no con otra cookie.
--}}
<aside id="cookieNotice" class="cookie-notice" role="region" aria-label="Aviso de cookies" hidden>
    <p class="cookie-notice__title">Este sitio usa cookies</p>
    <p class="cookie-notice__text">Usamos únicamente cookies técnicas indispensables para mantener tu sesión, proteger los formularios y que el asistente virtual recuerde la conversación. No usamos cookies de publicidad ni de rastreo.</p>
    <div class="cookie-notice__actions">
        <button type="button" class="cookie-notice__button" id="cookieNoticeAccept">Entendido</button>
        <a href="{{ route('privacy') }}#cookies">Más información</a>
    </div>
</aside>
<script>
(function () {
    var notice = document.getElementById('cookieNotice');
    var storageKey = 'cookieNoticeSeen';
    try { if (localStorage.getItem(storageKey) === '1') { return; } } catch (e) {}
    notice.hidden = false;
    document.getElementById('cookieNoticeAccept').addEventListener('click', function () {
        notice.hidden = true;
        try { localStorage.setItem(storageKey, '1'); } catch (e) {}
    });
})();
</script>
