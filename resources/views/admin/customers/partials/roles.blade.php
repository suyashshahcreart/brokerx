@php
    $displayLimit = 3;
    $total = $roles->count();
    $shown = $roles->take($displayLimit);
@endphp

<div class="d-flex flex-wrap align-items-center gap-1">
    @forelse($shown as $role)
        <span class="badge bg-soft-primary text-primary">{{ $role->name }}</span>
    @empty
        <span class="text-muted">No roles</span>
    @endforelse

    @if($total > $displayLimit)
        <span class="badge bg-soft-secondary text-secondary">+{{ $total - $displayLimit }} more</span>
    @endif
</div>

