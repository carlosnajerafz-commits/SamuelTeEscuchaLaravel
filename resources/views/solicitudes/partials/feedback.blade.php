@if ($errors->any())
    <div role="alert" class="solicitud-feedback solicitud-feedback--error" tabindex="-1">
        <strong>Revisa los siguientes datos para enviar tu solicitud:</strong>
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        @if ($module === 'quejas')<p>Vuelve a adjuntar la fotografía antes de enviar.</p>@endif
    </div>
@endif
@if (session('folio'))
    <section role="status" class="solicitud-feedback solicitud-feedback--success">
        <h2>¡Solicitud registrada!</h2>
        <p>Tu folio de seguimiento es</p>
        <p class="solicitud-folio">{{ session('folio') }}</p>
        <p>Guárdalo junto con el celular que registraste: con ambos puedes consultar el avance en el asistente <strong>(botón de abajo a la derecha)</strong> o llamando al 55 2172 4723.</p>
    </section>
@elseif (session('success'))
    <div role="status" class="solicitud-feedback solicitud-feedback--success">{{ session('success') }}</div>
@endif
