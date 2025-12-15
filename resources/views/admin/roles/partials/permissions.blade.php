@php
    $displayLimit = 3;
    $total = $permissions->count();
    $shown = $permissions->take($displayLimit);
@endphp

<div class="d-flex flex-wrap align-items-center gap-1">
    @foreach($shown as $permission)
        <span class="badge bg-soft-info text-info">{{ $permission }}</span>
    @endforeach

    @if($total > $displayLimit)
        <span class="badge bg-soft-secondary text-secondary">+{{ $total - $displayLimit }} more</span>
    @endif
</div>

