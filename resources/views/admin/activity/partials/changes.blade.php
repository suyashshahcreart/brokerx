@php
    $props = $activity->properties->toArray();
@endphp

@if(isset($props['changes']) && !empty($props['changes']))
    <div class="changes-container">
        @foreach($props['changes'] as $field => $change)
            <div class="mb-2 p-2 border rounded">
                <strong class="text-capitalize">{{ str_replace('_', ' ', $field) }}:</strong>
                <div class="d-flex gap-3 mt-1">
                    <div class="flex-fill">
                        <small class="text-danger">
                            <strong>Old:</strong>
                            {{ is_array($change['old'] ?? null) ? implode(', ', $change['old']) : ($change['old'] ?? 'N/A') }}
                        </small>
                    </div>
                    <div class="flex-fill">
                        <small class="text-success">
                            <strong>New:</strong>
                            {{ is_array($change['new'] ?? null) ? implode(', ', $change['new']) : ($change['new'] ?? 'N/A') }}
                        </small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@elseif(isset($props['after']) && ($props['event'] ?? $activity->description) === 'created')
    <div class="changes-container">
        @foreach($props['after'] as $field => $value)
            <div class="mb-1">
                <strong class="text-capitalize">{{ str_replace('_', ' ', $field) }}:</strong>
                <span class="text-success">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
            </div>
        @endforeach
    </div>
@elseif(isset($props['before']) && ($props['event'] ?? $activity->description) === 'deleted')
    <div class="changes-container">
        @foreach($props['before'] as $field => $value)
            <div class="mb-1">
                <strong class="text-capitalize">{{ str_replace('_', ' ', $field) }}:</strong>
                <span class="text-danger">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
            </div>
        @endforeach
    </div>
@elseif(isset($props['before']) && isset($props['after']))
    <div class="row changes-container">
        <div class="col-6">
            <strong class="text-danger">Before:</strong>
            <ul class="list-unstyled small mb-0">
                @foreach($props['before'] as $field => $value)
                    <li>
                        <strong>{{ str_replace('_', ' ', $field) }}:</strong>
                        {{ is_array($value) ? implode(', ', $value) : $value }}
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="col-6">
            <strong class="text-success">After:</strong>
            <ul class="list-unstyled small mb-0">
                @foreach($props['after'] as $field => $value)
                    <li>
                        <strong>{{ str_replace('_', ' ', $field) }}:</strong>
                        {{ is_array($value) ? implode(', ', $value) : $value }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@else
    <small class="text-muted">No changes recorded</small>
@endif

