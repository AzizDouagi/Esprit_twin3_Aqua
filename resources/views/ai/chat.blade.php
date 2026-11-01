@extends('layouts.admin')
@section('page-title', 'Assistant AquaSecure')
@section('content')
<div class="alert alert-info">Généré par IA — réponses à vérifier. L’assistant consulte des statistiques et des relevés via des outils en lecture seule. Mode local par défaut.</div>
<div id="chat-history" class="border rounded bg-white p-3 mb-3" role="log" aria-live="polite"></div>
<form id="chat-form"><label for="chat-input" class="form-label">Votre question</label><textarea class="form-control mb-2" id="chat-input" maxlength="2000" required placeholder="Combien d’incidents critiques sont ouverts ?"></textarea><button class="btn btn-aqua">Envoyer</button></form>
<p id="chat-status" role="status"></p>
@push('scripts')
<script>
const messages = [];
document.getElementById('chat-form').addEventListener('submit', async function (event) {
 event.preventDefault(); const button = this.querySelector('button'); const input = document.getElementById('chat-input'); const status = document.getElementById('chat-status');
 const question = input.value.trim(); if (!question) return;
 button.disabled = true; status.textContent = 'Réponse en cours…';
 const conversation = [...messages.slice(-18), {role:'user',content:question}];
 try {
  const response = await fetch(@json(route('ai.chat.store')), {method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},body:JSON.stringify({messages:conversation})});
  const result = await response.json(); if (!response.ok) throw new Error(result.message || 'Service indisponible');
  messages.push({role:'user',content:question},{role:'assistant',content:result.text.slice(0,2000)});
  const p = document.createElement('p'); p.style.whiteSpace = 'pre-wrap'; p.textContent = 'Vous : '+question+'\nAssistant ('+result.provider+(result.degraded ? ', mode dégradé' : '')+') : '+result.text;
  document.getElementById('chat-history').append(p); input.value = ''; status.textContent = '';
 } catch(error) { status.textContent = error.message; } finally { button.disabled = false; }
});
</script>
@endpush
@endsection
