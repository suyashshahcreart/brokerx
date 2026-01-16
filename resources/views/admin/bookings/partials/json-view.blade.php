<div class="mt-3">
    <h5>Booking JSON Data</h5>
    <pre class="bg-light p-3 rounded border" style="font-size: 13px; max-height: 400px; overflow: auto;">{{ json_encode($booking->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</div>
