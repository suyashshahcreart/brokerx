@if($qrs->isEmpty())
    <div class="col-12 text-center text-muted py-5">
        <i class="ri-inbox-line" style="font-size: 48px; opacity: 0.3;"></i>
        <div class="mt-3">No QR codes found.</div>
    </div>
@else
    @foreach($qrs as $qr)
        @php
            $isSelected = in_array($qr->id, $selectedIds ?? []);
        @endphp
        <x-qr-grid-card :qr="$qr" :isSelected="$isSelected" />
    @endforeach
@endif
