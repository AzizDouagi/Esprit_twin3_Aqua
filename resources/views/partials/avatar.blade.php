@php($size = $size ?? 40)
@if ($user->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="Photo de {{ $user->name }}" class="rounded-circle object-fit-cover" style="width: {{ $size }}px; height: {{ $size }}px;">
@else
    <span class="rounded-circle d-inline-flex align-items-center justify-content-center fw-semibold text-white" style="width: {{ $size }}px; height: {{ $size }}px; background: #0891b2; font-size: {{ $size * 0.4 }}px;">{{ $user->initiales() }}</span>
@endif
