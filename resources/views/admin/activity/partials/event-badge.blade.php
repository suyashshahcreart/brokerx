@php
    $event = strtolower($event ?? 'other');
    $badgeClass = [
        'created' => 'bg-success',
        'updated' => 'bg-primary',
        'deleted' => 'bg-danger',
    ][$event] ?? 'bg-secondary';
@endphp

<span class="badge {{ $badgeClass }} text-capitalize">{{ $event }}</span>

