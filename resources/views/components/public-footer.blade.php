<footer class="site-footer" aria-label="Información y enlaces del sitio">
    <div class="site-footer__grid">
        <div class="site-footer__identity">
            <a href="{{ route('home') }}" class="site-footer__brand">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Samuel Hernández" width="180" height="78" loading="lazy">
                <strong>Samuel Te Escucha</strong>
            </a>
            <p>Oficina virtual de atención ciudadana.</p>
            <p>Distrito XXXIII · Estado de México</p>
        </div>
        <nav aria-label="Mapa del sitio">
            <h2>Mapa del sitio</h2>
            <ul>
                <li><a href="{{ route('home') }}">Inicio</a></li>
                @foreach(config('samuel.modules') as $key => $item)
                    <li><a href="{{ route('solicitudes.create', $key) }}">{{ $item['title'] }}</a></li>
                @endforeach
                <li><a href="{{ route('login') }}">Acceso del personal</a></li>
            </ul>
        </nav>
        <div>
            <h2>Contacto</h2>
            <ul>
                <li><a href="tel:+525521724723">Teléfono: 55 2172 4723</a></li>
                <li><a href="https://wa.me/525521724723" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
            </ul>
            <h2 class="site-footer__social-title">Síguenos</h2>
            <ul class="site-footer__social">
                <li><a href="https://www.facebook.com/share/17BRGoeBCM/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer">Facebook</a></li>
                <li><a href="https://www.instagram.com/samueledomex?igsh=dXR2cGpmYm1rejEy" target="_blank" rel="noopener noreferrer">Instagram</a></li>
            </ul>
        </div>
        <section aria-label="Conócenos">
            <h2>Conócenos</h2><p>Samuel Te Escucha es la oficina virtual de atención ciudadana del Diputado Samuel Hernández Cruz.</p><p>Un espacio para enviar solicitudes, gestionar citas y consultar apoyos y jornadas del Comedor Solidario.</p><h2 class="site-footer__social-title">Privacidad</h2>
            <ul><li><a href="{{ route('privacy') }}">Aviso de privacidad</a></li></ul>
        </section>
    </div>
    <div class="site-footer__bottom">
        <div class="site-footer__bottom-inner">
            <p>© {{ date('Y') }} Samuel Te Escucha</p>
            @if(config('samuel.developer_name'))
                <p>Desarrollado por
                    @if(config('samuel.developer_url'))
                        <a href="{{ config('samuel.developer_url') }}" target="_blank" rel="noopener noreferrer">{{ config('samuel.developer_name') }}</a>
                    @else
                        <strong>{{ config('samuel.developer_name') }}</strong>
                    @endif
                </p>
            @endif
        </div>
    </div>
</footer>


