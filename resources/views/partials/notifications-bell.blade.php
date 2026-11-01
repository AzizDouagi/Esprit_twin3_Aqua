@auth
    @php($unread = auth()->user()->unreadNotifications()->latest()->take(5)->get())
    @php($unreadCount = auth()->user()->unreadNotifications()->count())
    <div class="dropdown">
        <a href="#" class="position-relative text-decoration-none {{ $light ?? false ? 'text-white' : 'text-dark' }}" data-bs-toggle="dropdown" aria-label="Notifications">
            <i class="bi bi-bell fs-5"></i>
            @if ($unreadCount)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unreadCount }}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 320px">
            <div class="px-3 py-2 border-bottom fw-semibold">Notifications</div>
            @forelse ($unread as $n)
                <form method="POST" action="{{ route('notifications.read', $n->id) }}">@csrf
                    <button class="dropdown-item small text-wrap">{{ $n->data['message'] ?? 'Notification' }}<div class="text-secondary">{{ $n->created_at->diffForHumans() }}</div></button>
                </form>
            @empty
                <div class="px-3 py-3 small text-secondary">Aucune notification non lue.</div>
            @endforelse
            <a class="dropdown-item text-center small border-top" href="{{ route('notifications.index') }}">Voir toutes</a>
        </div>
    </div>
@endauth
