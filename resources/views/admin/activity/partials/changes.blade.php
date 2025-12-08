@php
    $props = $activity->properties->toArray();
    
    // Helper function to format value for display
    $formatValue = function($value) {
        if (is_null($value)) {
            return 'N/A';
        }
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (is_array($value)) {
            // Check if it's an associative array or nested array
            if (array_keys($value) !== range(0, count($value) - 1)) {
                // It's associative or nested - show as JSON
                return json_encode($value, JSON_PRETTY_PRINT);
            }
            // Simple array - join with comma
            return implode(', ', array_map(function($item) {
                return is_array($item) ? json_encode($item) : $item;
            }, $value));
        }
        return $value;
    };
@endphp

@if(isset($props['changes']) && !empty($props['changes']))
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th style="width: 40%;">Field</th>
                    <th style="width: 30%;" class="text-danger">Old</th>
                    <th style="width: 30%;" class="text-success">New</th>
                </tr>
            </thead>
            <tbody>
                @foreach($props['changes'] as $field => $change)
                    @php
                        $oldValue = $formatValue($change['old'] ?? null);
                        $newValue = $formatValue($change['new'] ?? null);
                        // Skip if both are N/A or identical
                        $skipField = ($oldValue === 'N/A' && $newValue === 'N/A') || ($oldValue === $newValue);
                    @endphp
                    
                    @if(!$skipField)
                        <tr>
                            <td class="fw-bold text-capitalize">{{ str_replace('_', ' ', $field) }}</td>
                            <td class="text-danger"><small>{{ $oldValue }}</small></td>
                            <td class="text-success"><small>{{ $newValue }}</small></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@elseif(isset($props['after']) && ($props['event'] ?? $activity->description) === 'created')
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th style="width: 40%;">Field</th>
                    <th style="width: 60%;" class="text-success">Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($props['after'] as $field => $value)
                    @php
                        $formattedValue = $formatValue($value);
                        // Skip if N/A or empty
                        $skipField = $formattedValue === 'N/A' || empty($formattedValue);
                    @endphp
                    
                    @if(!$skipField)
                        <tr>
                            <td class="fw-bold text-capitalize">{{ str_replace('_', ' ', $field) }}</td>
                            <td class="text-success"><small>{{ $formattedValue }}</small></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@elseif(isset($props['before']) && ($props['event'] ?? $activity->description) === 'deleted')
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th style="width: 40%;">Field</th>
                    <th style="width: 60%;" class="text-danger">Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($props['before'] as $field => $value)
                    @php
                        $formattedValue = $formatValue($value);
                        // Skip if N/A or empty
                        $skipField = $formattedValue === 'N/A' || empty($formattedValue);
                    @endphp
                    
                    @if(!$skipField)
                        <tr>
                            <td class="fw-bold text-capitalize">{{ str_replace('_', ' ', $field) }}</td>
                            <td class="text-danger"><small>{{ $formattedValue }}</small></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@elseif(isset($props['before']) && isset($props['after']))
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th style="width: 40%;">Field</th>
                    <th style="width: 30%;" class="text-danger">Before</th>
                    <th style="width: 30%;" class="text-success">After</th>
                </tr>
            </thead>
            <tbody>
                @foreach($props['before'] as $field => $beforeValue)
                    @php
                        $afterValue = $props['after'][$field] ?? null;
                        $oldFormatted = $formatValue($beforeValue);
                        $newFormatted = $formatValue($afterValue);
                        // Skip if both are N/A or identical
                        $skipField = ($oldFormatted === 'N/A' && $newFormatted === 'N/A') || ($oldFormatted === $newFormatted);
                    @endphp
                    
                    @if(!$skipField)
                        <tr>
                            <td class="fw-bold text-capitalize">{{ str_replace('_', ' ', $field) }}</td>
                            <td class="text-danger"><small>{{ $oldFormatted }}</small></td>
                            <td class="text-success"><small>{{ $newFormatted }}</small></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <small class="text-muted">No changes recorded</small>
@endif

