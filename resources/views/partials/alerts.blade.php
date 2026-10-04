@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-2">
            <i class="bi bi-exclamation-triangle me-2"></i>Veuillez corriger les erreurs suivantes :
        </div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
