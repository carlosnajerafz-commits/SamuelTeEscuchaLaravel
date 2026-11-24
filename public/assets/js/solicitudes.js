document.addEventListener('DOMContentLoaded', () => {
    const cp = document.getElementById('codigo_postal');
    const colony = document.getElementById('colonia');
    const town = document.getElementById('municipio');
    if (cp && colony && town) {
        let entries = [];
        let serial = 0;
        const initialColony = colony.dataset.old || '';
        async function lookup(restore = false) {
            const current = ++serial;
            cp.value = cp.value.replace(/[^0-9]/g, '').slice(0, 5);
            entries = [];
            town.value = '';
            colony.replaceChildren(new Option('Seleccione una colonia', ''));
            if (cp.value.length !== 5) return;
            colony.replaceChildren(new Option('Buscando colonias…', ''));
            try {
                const response = await fetch(`${cp.dataset.lookupUrl}?cp=${encodeURIComponent(cp.value)}`, {headers: {Accept: 'application/json'}});
                if (!response.ok) throw new Error('lookup');
                const data = await response.json();
                if (current !== serial) return;
                if (!Array.isArray(data.resultados)) throw new Error('lookup');
                entries = data.resultados;
                colony.replaceChildren(new Option(entries.length ? 'Seleccione una colonia' : 'Código postal no encontrado', ''));
                [...new Set(entries.map(row => row.colonia))].forEach(name => colony.add(new Option(name, name)));
                if (restore) colony.value = initialColony;
                updateTown();
            } catch {
                if (current === serial) colony.replaceChildren(new Option('Error al buscar CP. Vuelve a capturarlo.', ''));
            }
        }
        function updateTown() {
            town.value = entries.find(row => row.colonia === colony.value)?.municipio || entries[0]?.municipio || '';
        }
        cp.addEventListener('input', () => lookup());
        colony.addEventListener('change', updateTown);
        if (cp.value) lookup(true);
    }

    const cards = [...document.querySelectorAll('.evento-card')];
    function selectEvent(card) {
        if (card.dataset.sinCupo === '1') return;
        cards.forEach(item => {
            item.classList.toggle('selected', item === card);
            item.setAttribute('aria-checked', String(item === card));
        });
        document.getElementById('eventoIdInput').value = card.dataset.id;
        const registration = document.getElementById('formularioRegistro');
        registration.style.display = '';
        registration.scrollIntoView({behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start'});
    }
    cards.forEach(card => {
        card.addEventListener('click', () => selectEvent(card));
        card.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectEvent(card);
            }
        });
    });
});
