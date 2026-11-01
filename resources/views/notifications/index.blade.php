@extends(auth()->user()->accesBackOffice() ? 'layouts.admin' : 'layouts.front')
@section('title', 'Notifications')
@section('eyebrow', 'Compte')
@section('page-title', 'Notifications')
@section('content')
<div class="{{ auth()->user()->accesBackOffice() ? '' : 'container py-4' }}">
    <div class="d-flex justify-content-between mb-3">
        <h1 class="h4 mb-0">{{ auth()->user()->accesBackOffice() ? '' : 'Notifications' }}</h1>
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf
            <button class="btn btn-sm btn-outline-secondary">Tout marquer comme lu</button></form>
    </div>
    <div class="list-group">
        @forelse ($notifications as $n)
            <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="list-group-item list-group-item-action {{ $n->read_at ? '' : 'fw-semibold bg-light' }}">
                @csrf
                <button class="btn btn-link text-start text-decoration-none text-dark w-100 p-0">
                    <i class="bi bi-{{ str_contains($n->data['type'] ?? '', 'critique') ? 'exclamation-triangle text-danger' : 'bell' }} me-2"></i>{{ $n->data['message'] ?? 'Notification' }}
                    <span class="small text-secondary float-end">{{ $n->created_at->diffForHumans() }}</span>
                </button>
            </form>
        @empty
            <div class="list-group-item text-secondary">Aucune notification.</div>
        @endforelse
    </div>
    <div class="mt-3">{{ $notifications->links() }}</div>
</div>
@endsection
