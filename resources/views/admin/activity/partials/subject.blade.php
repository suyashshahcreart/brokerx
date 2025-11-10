<div>
    <strong>{{ class_basename($activity->subject_type) ?? 'N/A' }}</strong><br>
    <small class="text-muted">ID: {{ $activity->subject_id ?? ($activity->properties['deleted_id'] ?? 'N/A') }}</small>
</div>

