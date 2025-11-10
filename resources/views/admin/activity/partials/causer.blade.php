<div>
    {{ $activity->causer?->name ?? 'System' }}<br>
    <small class="text-muted">{{ $activity->causer?->email ?? '' }}</small>
</div>

