<div class="card border-0">
    <div class="card-body">
        @if(!($tour ?? null))
            <div class="alert alert-warning mb-0">
                Tour not found for this booking. Please upload the tour first, then open this tab again.
            </div>
        @else
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-2">
                <div class="text-muted small">
                    This loads the remote `index.php` from FTP.
                </div>
                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-soft-secondary btn-sm d-none" id="ftpIndexOpenUrlBtn" target="_blank" rel="noopener">
                        <i class="ri-external-link-line me-1"></i> Open URL
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" id="ftpIndexReloadBtn">
                        <i class="ri-refresh-line me-1"></i> Reload
                    </button>
                </div>
            </div>

            <div class="mb-2">
                <div class="small">
                    <div><span class="text-muted">FTP Path:</span> <span class="fw-semibold" id="ftpIndexPath">-</span></div>
                    <div><span class="text-muted">FTP URL:</span> <span class="fw-semibold" id="ftpIndexUrlText">-</span></div>
                </div>
            </div>

            <div class="alert alert-info d-none" id="ftpIndexInfo"></div>
            <div class="alert alert-danger d-none" id="ftpIndexError"></div>

            <div class="border rounded bg-light-subtle p-2" style="max-height: 65vh; overflow: auto;">
                <pre class="mb-0" id="ftpIndexContent" style="white-space: pre; font-size: 12px; line-height: 1.4;">Click "Reload" or open this tab to fetch FTP file…</pre>
            </div>
        @endif
    </div>
</div>

