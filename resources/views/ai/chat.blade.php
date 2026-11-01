@extends('layouts.admin')
@section('title', 'Assistant IA')
@section('eyebrow', 'Pilotage intelligent')
@section('page-title', 'Assistant AquaSecure')
@section('actions')
    <button class="btn btn-outline-secondary btn-sm" id="chat-reset" type="button"><i class="bi bi-plus-lg me-1"></i> Nouvelle conversation</button>
@endsection
@section('content')
<div id="assistant-chat" class="assistant" data-endpoint="{{ route('ai.chat.store') }}" data-csrf="{{ csrf_token() }}" data-user="{{ auth()->id() }}">
    <div class="assistant-intro">
        <p>Vos incidents, vos infrastructures, les bonnes informations pour décider.</p>
        <span class="assistant-mode"><span class="assistant-mode-dot"></span>{{ $provider === 'ollama' ? ucfirst(explode(':', $model)[0]).' · IA locale' : ($provider === 'anthropic' ? 'Claude · assistant IA' : 'Mode démonstration') }}</span>
    </div>
    <div class="assistant-grid">
        <section class="assistant-panel" aria-label="Conversation avec AquaSecure">
            <header class="assistant-panel-header">
                <div class="assistant-identity"><span class="assistant-avatar"><i class="bi bi-stars"></i></span><div><strong>Votre copilote réseau</strong><span>Des réponses fondées sur vos données</span></div></div>
                <span class="assistant-access"><i class="bi bi-shield-check"></i> Consultation</span>
            </header>
            <div class="assistant-feed" id="chat-feed" role="log" aria-label="Messages de la conversation" aria-live="polite" aria-relevant="additions">
                <div class="assistant-welcome" id="chat-welcome">
                    <span class="assistant-welcome-icon"><i class="bi bi-water"></i></span>
                    <h2>Comment puis-je vous aider ?</h2>
                    <p>Faites le point sur les incidents ou identifiez les infrastructures à surveiller.</p>
                    <div class="assistant-suggestions">
                        <button type="button" data-question="Combien d’incidents critiques sont actuellement actifs ?"><i class="bi bi-exclamation-triangle"></i><strong>Incidents critiques</strong><span>Connaître la situation actuelle</span><i class="bi bi-arrow-up-right"></i></button>
                        <button type="button" data-question="Quelles infrastructures faut-il surveiller en priorité et pourquoi ?"><i class="bi bi-activity"></i><strong>Priorités du réseau</strong><span>Repérer les risques et leurs facteurs</span><i class="bi bi-arrow-up-right"></i></button>
                        <button type="button" data-question="Quels sont les derniers incidents enregistrés ?"><i class="bi bi-clock-history"></i><strong>Derniers signalements</strong><span>Consulter les incidents récents</span><i class="bi bi-arrow-up-right"></i></button>
                        <button type="button" data-question="Quelles infrastructures sont enregistrées sur le réseau ?"><i class="bi bi-buildings"></i><strong>Explorer le réseau</strong><span>Retrouver une infrastructure</span><i class="bi bi-arrow-up-right"></i></button>
                    </div>
                </div>
                <div id="chat-messages"></div>
            </div>
            <div class="assistant-compose-area">
                <form id="chat-form" class="assistant-composer">
                    <label for="chat-input" class="visually-hidden">Votre question</label>
                    <textarea id="chat-input" rows="1" maxlength="2000" placeholder="Posez une question sur votre réseau…" required></textarea>
                    <button type="submit" id="chat-send" class="assistant-send" aria-label="Envoyer le message" title="Envoyer"><i class="bi bi-arrow-up"></i></button>
                    <button type="button" id="chat-stop" class="assistant-stop" aria-label="Interrompre la réponse" title="Interrompre" hidden><i class="bi bi-stop-fill"></i></button>
                </form>
                <div class="assistant-compose-meta"><span><i class="bi bi-stars"></i> Réponses générées par IA, à vérifier avant toute intervention.</span><span id="chat-count">0 / 2 000</span></div>
                <p class="assistant-keyboard">Entrée pour envoyer <span>·</span> Maj + Entrée pour une nouvelle ligne</p>
                <p id="chat-status" class="visually-hidden" role="status"></p>
            </div>
        </section>
        <aside class="assistant-context" aria-label="Contexte du réseau">
            <section class="assistant-context-card">
                <div class="assistant-context-heading"><span class="assistant-live-dot"></span><h2>Votre réseau à l’instant</h2></div>
                <div class="assistant-kpi"><span class="assistant-kpi-icon danger"><i class="bi bi-exclamation-triangle"></i></span><div><strong data-stat="incidents_critiques">{{ $stats['incidents_critiques'] }}</strong><span>incidents critiques actifs</span></div></div>
                <div class="assistant-kpi"><span class="assistant-kpi-icon warning"><i class="bi bi-clipboard-pulse"></i></span><div><strong data-stat="incidents_actifs">{{ $stats['incidents_actifs'] }}</strong><span>incidents non résolus</span></div></div>
                <div class="assistant-kpi"><span class="assistant-kpi-icon aqua"><i class="bi bi-buildings"></i></span><div><strong data-stat="infrastructures">{{ $stats['infrastructures'] }}</strong><span>infrastructures suivies</span></div></div>
                <a href="{{ route('dashboard') }}" class="assistant-context-link">Ouvrir le tableau de bord <i class="bi bi-arrow-right"></i></a>
            </section>
            <section class="assistant-context-card assistant-help">
                <span class="assistant-help-icon"><i class="bi bi-lightbulb"></i></span>
                <h2>Une question précise,<br>une réponse plus utile.</h2>
                <p>Précisez une gravité ou le nom d’une infrastructure. Vous pouvez ensuite affiner votre question dans la conversation.</p>
                <button type="button" data-question="Liste les derniers incidents de gravité Critique.">« Montre-moi les derniers incidents critiques. » <i class="bi bi-arrow-up-right"></i></button>
            </section>
            <div class="assistant-privacy"><i class="bi bi-lock"></i><p>L’assistant consulte les informations auxquelles votre rôle donne accès. Vos échanges ne modifient aucune donnée.</p></div>
        </aside>
    </div>
</div>
@endsection
