@can('ai.use')
<button type="button" class="btn btn-sm btn-outline-primary mt-2" id="ai-classify">Suggérer type et gravité avec l’IA</button>
<p id="ai-classify-result" class="small mt-2" role="status" aria-live="polite"></p>
@push('scripts')
<script>
document.getElementById('ai-classify').addEventListener('click', async function () {
    const output = document.getElementById('ai-classify-result');
    this.disabled = true; output.textContent = 'Classification en cours…';
    try {
        const response = await fetch(@json(route('ai.classify')), { method: 'POST', headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())}, body: JSON.stringify({titre: document.getElementById('titre').value, description: document.getElementById('description').value}) });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Classification indisponible.');
        document.getElementById('type').value = data.data.type;
        document.getElementById('gravite').value = data.data.gravite;
        output.textContent = 'Généré par IA (' + data.meta.provider + (data.meta.degraded ? ', mode dégradé' : '') + ') — ' + data.data.explication + ' Vérifiez avant d’enregistrer.';
    } catch (error) { output.textContent = error.message; }
    finally { this.disabled = false; }
});
</script>
@endpush
@endcan
