const root = document.getElementById('assistant-chat');
if (root) {
    const feed = document.getElementById('chat-feed');
    const messagesEl = document.getElementById('chat-messages');
    const welcome = document.getElementById('chat-welcome');
    const form = document.getElementById('chat-form');
    const input = document.getElementById('chat-input');
    const send = document.getElementById('chat-send');
    const stop = document.getElementById('chat-stop');
    const status = document.getElementById('chat-status');
    const storageKey = `aquasecure:assistant:v2:${root.dataset.user}`;
    let messages = [];
    let controller = null;
    let generation = 0;
    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    const icon = (name) => element('i', `bi bi-${name}`);
    const scroll = () => { feed.scrollTop = feed.scrollHeight; };
    const save = () => { try { sessionStorage.setItem(storageKey, JSON.stringify(messages.slice(-20))); } catch { /* Stockage privé indisponible. */ } };
    const resize = () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 145)}px`;
        document.getElementById('chat-count').textContent = `${input.value.length.toLocaleString('fr-FR')} / 2 000`;
        send.disabled = !input.value.trim() || controller !== null;
    };
    // Formatage limité : chaque texte reste un nœud DOM, aucun HTML fourni par le modèle.
    function inline(node, value) {
        for (const part of value.split(/(\*\*[^*]+\*\*|`[^`]+`)/g)) {
            node.append(part.startsWith('**') && part.endsWith('**') ? element('strong', '', part.slice(2, -2)) : part.startsWith('`') && part.endsWith('`') ? element('code', '', part.slice(1, -1)) : document.createTextNode(part));
        }
    }
    function renderText(node, value) {
        node.replaceChildren();
        let paragraph = null;
        let list = null;
        for (const line of value.split('\n')) {
            const text = line.trim();
            if (!text) { paragraph = null; list = null; continue; }
            if (/^#{1,3}\s/.test(text)) {
                const heading = element('h3'); inline(heading, text.replace(/^#{1,3}\s+/, '')); node.append(heading); paragraph = null; list = null;
            } else if (/^[-*•]\s|^\d+[.)]\s/.test(text)) {
                const type = /^\d/.test(text) ? 'ol' : 'ul';
                if (!list || list.tagName.toLowerCase() !== type) { list = element(type); node.append(list); }
                const item = element('li'); inline(item, text.replace(/^(?:[-*•]|\d+[.)])\s+/, '')); list.append(item); paragraph = null;
            } else {
                list = null;
                if (!paragraph) { paragraph = element('p'); node.append(paragraph); } else paragraph.append(element('br'));
                inline(paragraph, text);
            }
        }
    }
    function frame(role, time = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})) {
        welcome.hidden = true;
        const row = element('article', `chat-message ${role}`);
        const body = element('div', 'chat-message-body');
        const meta = element('div', 'chat-message-meta');
        meta.append(element('strong', '', role === 'user' ? 'Vous' : 'AquaSecure'), element('span', '', time));
        const content = element('div', 'chat-message-content');
        body.append(meta, content);
        if (role === 'assistant') { const avatar = element('span', 'chat-message-avatar'); avatar.append(icon('stars')); row.append(avatar); }
        row.append(body); messagesEl.append(row); scroll();
        return {row, body, meta, content, time};
    }
    function answer(view, result) {
        if (result.stats) root.querySelectorAll('[data-stat]').forEach(node => {
            const value = result.stats[node.dataset.stat];
            if (Number.isInteger(value) && value >= 0) node.textContent = value.toLocaleString('fr-FR');
        });
        renderText(view.content, result.text);
        if (Array.isArray(result.sources) && result.sources.length) {
            const sources = element('div', 'chat-message-sources');
            for (const source of result.sources.slice(0, 6)) {
                try {
                    const url = new URL(source.url, location.origin);
                    if (url.origin !== location.origin || !url.pathname.startsWith('/admin')) continue;
                    const link = element('a'); link.href = url.href; link.append(icon('database-check'), document.createTextNode(source.label)); sources.append(link);
                } catch { /* Ignorer une source invalide. */ }
            }
            view.body.append(sources);
        }
        if (result.degraded) view.body.append(element('div', 'chat-message-note', 'Réponse de secours : le modèle IA est momentanément indisponible.'));
        else if (result.provider === 'fake') view.body.append(element('div', 'chat-message-note', 'Mode démonstration · réponse locale sans modèle génératif.'));
        const copy = element('button', 'chat-message-copy'); copy.type = 'button'; copy.title = 'Copier la réponse'; copy.setAttribute('aria-label', 'Copier la réponse'); copy.append(icon('copy'));
        copy.addEventListener('click', async () => { try { await navigator.clipboard.writeText(result.text); copy.replaceChildren(icon('check2')); } catch { status.textContent = 'La copie n’est pas disponible dans ce navigateur.'; } });
        view.meta.append(copy); scroll();
    }
    function setBusy(value) {
        stop.hidden = !value; send.hidden = value;
        document.getElementById('chat-reset').disabled = value;
        root.querySelectorAll('[data-question]').forEach(button => { button.disabled = value; });
        form.setAttribute('aria-busy', String(value)); resize();
    }
    async function ask(question, existingView = null) {
        if (controller || !question.trim()) return;
        if (!existingView) { const userView = frame('user'); userView.content.textContent = question; }
        const view = existingView || frame('assistant');
        view.row.classList.remove('error'); view.content.replaceChildren();
        const thinking = element('div', 'chat-thinking'); const dots = element('span', 'chat-thinking-dots'); dots.append(element('span'), element('span'), element('span'));
        const thinkingText = element('span', '', 'Je consulte les données du réseau…'); thinking.append(dots, thinkingText); view.content.append(thinking);
        controller = new AbortController(); const current = ++generation; const signal = controller.signal;
        const timeout = setTimeout(() => controller?.abort('timeout'), 70000);
        const progress = setTimeout(() => { thinkingText.textContent = 'Je prépare une réponse à partir des informations trouvées…'; }, 7000);
        input.value = ''; setBusy(true); status.textContent = 'AquaSecure prépare sa réponse.'; scroll();
        try {
            const conversation = [...messages.slice(-18).map(({role, content}) => ({role, content: content.slice(0, 2000)})), {role: 'user', content: question}];
            const response = await fetch(root.dataset.endpoint, {method: 'POST', signal, headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf}, body: JSON.stringify({messages: conversation})});
            if (response.status === 419 || response.status === 401) throw new Error('Votre session a expiré. Rechargez la page pour vous reconnecter.');
            let result;
            try { result = await response.json(); } catch { throw new Error('Le serveur n’a pas pu terminer la réponse. Réessayez dans quelques instants.'); }
            if (!response.ok) throw new Error(result.message || 'L’assistant est momentanément indisponible.');
            if (typeof result.text !== 'string' || !result.text.trim()) throw new Error('L’assistant n’a pas renvoyé de réponse. Réessayez.');
            if (current !== generation) return;
            answer(view, result);
            messages.push({role: 'user', content: question}, {role: 'assistant', content: result.text, sources: result.sources, degraded: result.degraded, provider: result.provider});
            messages = messages.slice(-20); save(); status.textContent = 'La réponse d’AquaSecure est disponible.';
        } catch (error) {
            if (current !== generation) return;
            view.row.classList.add('error');
            view.content.textContent = signal.aborted ? (signal.reason === 'timeout' ? 'La réponse prend trop de temps. Réessayez avec une question plus précise.' : 'Réponse interrompue. Vous pouvez reformuler votre question.') : error.message;
            const retry = element('button', 'chat-retry', 'Réessayer'); retry.type = 'button'; retry.addEventListener('click', () => ask(question, view)); view.content.append(retry);
            status.textContent = view.content.firstChild?.textContent || 'Erreur de réponse.'; scroll();
        } finally {
            clearTimeout(timeout); clearTimeout(progress);
            if (current === generation) { controller = null; setBusy(false); input.focus({preventScroll: true}); }
        }
    }
    form.addEventListener('submit', event => { event.preventDefault(); if (form.reportValidity()) ask(input.value.trim()); });
    input.addEventListener('input', resize);
    input.addEventListener('keydown', event => { if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); if (!controller && input.value.trim()) form.requestSubmit(); } });
    root.querySelectorAll('[data-question]').forEach(button => button.addEventListener('click', () => ask(button.dataset.question)));
    stop.addEventListener('click', () => controller?.abort('cancel'));
    document.getElementById('chat-reset').addEventListener('click', () => { messages = []; save(); messagesEl.replaceChildren(); welcome.hidden = false; input.value = ''; resize(); input.focus({preventScroll: true}); });
    try {
        const saved = JSON.parse(sessionStorage.getItem(storageKey) || '[]');
        if (Array.isArray(saved)) {
            messages = saved.filter(m => m && ['user', 'assistant'].includes(m.role) && typeof m.content === 'string' && m.content.length <= 10000).slice(-20);
            for (const message of messages) { const view = frame(message.role); if (message.role === 'user') view.content.textContent = message.content; else answer(view, {...message, text: message.content}); }
        }
    } catch { messages = []; }
    resize();
}
