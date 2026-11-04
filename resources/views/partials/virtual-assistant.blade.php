@if((auth()->user()->role->role_name ?? '') === 'Contributor')
<style>
    #va-toggle {
        position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 1040;
        width: 56px; height: 56px; border-radius: 50%; border: 0;
        background: var(--bs-primary); color: #fff; font-size: 1.4rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .3); cursor: pointer;
    }
    #va-panel {
        position: fixed; right: 1.25rem; bottom: 5.5rem; z-index: 1040;
        width: 370px; max-width: calc(100vw - 2rem); max-height: 75vh;
        display: flex; flex-direction: column;
        background: var(--bs-body-bg); color: var(--bs-body-color);
        border: 1px solid var(--bs-border-color); border-radius: .75rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .3); overflow: hidden;
    }
    #va-panel.d-none { display: none !important; }
    #va-panel .va-header {
        display: flex; align-items: center; justify-content: space-between; gap: .5rem;
        padding: .6rem .8rem; background: var(--bs-primary); color: #fff;
    }
    #va-panel .va-header button {
        background: transparent; border: 0; color: #fff; font-size: .85rem; cursor: pointer;
    }
    #va-messages { flex: 1 1 auto; overflow-y: auto; padding: .75rem; }
    .va-msg { margin-bottom: .6rem; padding: .5rem .7rem; border-radius: .6rem; font-size: .9rem; max-width: 92%; }
    .va-user { margin-left: auto; background: var(--bs-primary); color: #fff; }
    .va-bot { background: var(--bs-tertiary-bg); color: var(--bs-body-color); border: 1px solid var(--bs-border-color); }
    .va-bot ol { padding-left: 1.2rem; margin: .4rem 0; }
    .va-card { margin-top: .5rem; padding-top: .5rem; border-top: 1px solid var(--bs-border-color); }
    .va-card:first-child { border-top: 0; padding-top: 0; margin-top: 0; }
    #va-chips { padding: 0 .75rem .5rem; display: flex; flex-wrap: wrap; gap: .35rem; }
    #va-chips button {
        font-size: .78rem; padding: .2rem .55rem; border-radius: 1rem; cursor: pointer;
        background: transparent; color: var(--bs-body-color); border: 1px solid var(--bs-border-color);
    }
    #va-form { display: flex; gap: .4rem; padding: .6rem .75rem; border-top: 1px solid var(--bs-border-color); }
    #va-form input { flex: 1 1 auto; }
</style>

<button type="button" id="va-toggle" aria-label="Abrir asistente virtual" aria-expanded="false" aria-controls="va-panel" title="Asistente virtual">
    <i class="fas fa-comments"></i>
</button>

<section id="va-panel" class="d-none" role="dialog" aria-label="Asistente virtual de la Mesa de Servicio">
    <div class="va-header">
        <strong><i class="fas fa-comments me-1"></i>Asistente virtual</strong>
        <span>
            <button type="button" id="va-reset" title="Empezar una consulta nueva">Nueva consulta</button>
            <button type="button" id="va-close" aria-label="Cerrar el asistente" title="Cerrar"><i class="fas fa-times"></i></button>
        </span>
    </div>
    <div id="va-messages" aria-live="polite"></div>
    <div id="va-chips"></div>
    <form id="va-form" autocomplete="off">
        <input type="text" id="va-input" class="form-control form-control-sm" maxlength="200" placeholder="Escribe tu pregunta..." aria-label="Tu pregunta">
        <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
    </form>
</section>

<script>
(function () {
    const endpoint = @json(route('assistant.ask'));
    const token = @json(csrf_token());
    const screen = @json(optional(request()->route())->getName());
    const suggestions = [
        'Cómo tomar un ticket',
        'Cómo solicitar unirme a un equipo',
        'Cómo actualizar el progreso',
        'Cómo transferir un ticket',
        'Cómo completar el flujo ADDIE'
    ];

    const toggle = document.getElementById('va-toggle');
    const panel = document.getElementById('va-panel');
    const messages = document.getElementById('va-messages');
    const chips = document.getElementById('va-chips');
    const form = document.getElementById('va-form');
    const input = document.getElementById('va-input');

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) { node.className = className; }
        if (text !== undefined && text !== null) { node.textContent = text; }
        return node;
    }

    function scrollDown() { messages.scrollTop = messages.scrollHeight; }

    function addUser(text) {
        messages.appendChild(el('div', 'va-msg va-user', text));
        scrollDown();
    }

    function addBot(text) {
        const node = el('div', 'va-msg va-bot', text);
        messages.appendChild(node);
        scrollDown();
        return node;
    }

    function link(href, label, className) {
        const a = el('a', className || 'btn btn-sm btn-outline-primary me-1 mt-1', label);
        a.href = href;
        return a;
    }

    function renderAnswer(data) {
        const bubble = el('div', 'va-msg va-bot');
        bubble.appendChild(el('div', null, data.message));

        if (data.found) {
            (data.results || []).forEach(function (item) {
                const card = el('div', 'va-card');
                card.appendChild(el('strong', null, item.title));
                if (item.summary) { card.appendChild(el('div', 'small text-muted', item.summary)); }

                const list = document.createElement('ol');
                (item.steps || []).forEach(function (step) { list.appendChild(el('li', null, step)); });
                card.appendChild(list);

                card.appendChild(link(item.url, 'Abrir artículo'));
                if (item.screen_url && item.screen_label) { card.appendChild(link(item.screen_url, 'Ir a: ' + item.screen_label)); }
                if (item.reviewed) { card.appendChild(el('div', 'small text-muted mt-1', 'Última revisión: ' + item.reviewed)); }
                bubble.appendChild(card);
            });
        } else {
            bubble.appendChild(el('div', 'mt-2', data.contact));
            if (data.kb_url) { bubble.appendChild(link(data.kb_url, 'Ver la base de conocimiento')); }
        }

        messages.appendChild(bubble);
        scrollDown();
    }

    async function ask(question) {
        addUser(question);
        const loading = addBot('Buscando en la base de conocimiento...');

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ q: question, screen: screen })
            });
            loading.remove();

            if (response.status === 429) { addBot('Hiciste muchas consultas seguidas. Espera un momento e inténtalo de nuevo.'); return; }
            if (response.status === 419) { addBot('Tu sesión expiró. Recarga la página para continuar.'); return; }
            if (!response.ok) { addBot('No pude procesar la consulta. Escríbela con al menos dos letras e inténtalo de nuevo.'); return; }

            renderAnswer(await response.json());
        } catch (error) {
            loading.remove();
            addBot('No fue posible conectar con el asistente. Revisa tu conexión.');
        }
    }

    function reset() {
        messages.innerHTML = '';
        addBot('Hola, soy el asistente de la Mesa de Servicio. Respondo con los artículos aprobados de la base de conocimiento. No ejecuto acciones ni consulto tickets: para eso te indico la pantalla correspondiente.');
    }

    function openPanel() {
        panel.classList.remove('d-none');
        toggle.setAttribute('aria-expanded', 'true');
        input.focus();
    }

    function closePanel() {
        panel.classList.add('d-none');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
    }

    suggestions.forEach(function (text) {
        const chip = el('button', null, text);
        chip.type = 'button';
        chip.addEventListener('click', function () { ask(text); });
        chips.appendChild(chip);
    });

    toggle.addEventListener('click', function () {
        if (panel.classList.contains('d-none')) { openPanel(); } else { closePanel(); }
    });
    document.getElementById('va-close').addEventListener('click', closePanel);
    document.getElementById('va-reset').addEventListener('click', reset);
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !panel.classList.contains('d-none')) { closePanel(); }
    });
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const question = input.value.trim();
        if (question.length < 2) { return; }
        input.value = '';
        ask(question);
    });

    reset();
})();
</script>
@endif