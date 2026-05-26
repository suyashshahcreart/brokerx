/** Shared ZIP progress labels and helpers (queue monitor + tour live link poll). */

export const PHASE_LABELS = {
    queued: 'Queued',
    job_start: 'Starting job',
    validate: 'Validating',
    zip_to_s3: 'Storing archive',
    s3_upload: 'Uploading to cloud storage',
    index_local: 'Building index.php',
    ftp_upload: 'Publishing to hosting',
    db_sync: 'Saving tour data',
    finalize: 'Finalizing',
    done: 'Completed',
    failed: 'Failed',
};

export function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
}

export function truncateMiddle(text, maxLen) {
    const s = String(text ?? '');
    if (s.length <= maxLen) return s;
    const half = Math.floor((maxLen - 3) / 2);
    return s.slice(0, half) + '…' + s.slice(s.length - half);
}

export function formatEtaSeconds(seconds) {
    if (seconds == null || Number.isNaN(seconds)) return 'Calculating…';
    const s = Math.max(0, Math.floor(seconds));
    if (s === 0) return 'Nearly done…';
    const m = Math.floor(s / 60);
    const r = s % 60;
    if (m === 0) return `~${r}s remaining`;
    return `~${m}m ${String(r).padStart(2, '0')}s remaining`;
}

/** elapsed string from millis since UTC start */
export function formatElapsedFromMs(ms) {
    if (ms == null || Number.isNaN(ms)) return '';
    const totalSeconds = Math.floor(Math.max(0, ms) / 1000);
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    if (minutes === 0) return `${seconds}s`;
    return `${minutes}m ${String(seconds).padStart(2, '0')}s`;
}

/**
 * @param {{
 *   tour_zip_status?: string,
 *   percent?: number,
 *   phase?: string|null,
 *   message?: string|null,
 *   items_done?: number,
 *   items_total?: number,
 *   eta_seconds?: number|null,
 *   current_item?: string|null
 * }|null|undefined} progress
 * @param {{ compact?: boolean }} opts
 */
export function renderTourZipProgressHtml(progress, opts = {}) {
    const compact = opts.compact ?? false;
    if (!progress || typeof progress !== 'object') {
        return '<span class="text-muted">—</span>';
    }

    const st = progress.tour_zip_status ?? 'pending';
    if (st !== 'processing' && st !== 'done' && st !== 'failed') {
        return '<span class="text-muted">—</span>';
    }

    const raw = Number.parseFloat(String(progress.percent ?? '0'));
    const pct = Math.max(0, Math.min(100, Number.isFinite(raw) ? raw : 0));
    const fmt = pct.toFixed(2);
    const phaseKey = progress.phase ?? '';
    const phaseLabel = PHASE_LABELS[phaseKey] || (phaseKey ? String(phaseKey).replace(/_/g, ' ') : '');
    const message = progress.message ?? '';
    const itemsDone = progress.items_done ?? 0;
    const itemsTotal = progress.items_total ?? 0;
    const etaSec = progress.eta_seconds;
    const currentItem = progress.current_item ?? '';

    if (st === 'failed') {
        const msg = message ? `: ${escapeHtml(truncateMiddle(message, 120))}` : '';
        return `<span class="text-danger small">Failed${msg}</span>`;
    }

    if (st === 'done') {
        return `<span class="text-success small"><strong>${escapeHtml(fmt)}%</strong> · completed</span>`;
    }

    const width = Math.max(0.05, pct);
    const h = compact ? '6px' : '8px';

    const fileLine =
        currentItem && !compact ?
            `<div class="text-truncate small text-muted mt-1" title="${escapeHtml(currentItem)}">${escapeHtml(truncateMiddle(currentItem, compact ? 30 : 50))}</div>`
        :   '';

    const etaPart = etaSec != null && pct > 1 ? formatEtaSeconds(etaSec) : 'Calculating ETA…';

    const msgPart = message && !compact ? `<div class="small text-muted">${escapeHtml(truncateMiddle(message, 100))}</div>` : '';

    return `
<div class="queue-tour-progress" style="min-width:140px;">
  ${phaseLabel ? `<div class="small fw-medium text-warning mb-1">${escapeHtml(phaseLabel)}</div>` : ''}
  ${msgPart}
  <div class="progress" style="height: ${h};">
    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
      role="progressbar" style="width: ${width}%; transition: width 0.4s ease;"
      aria-valuenow="${fmt}" aria-valuemin="0" aria-valuemax="100"></div>
  </div>
  <small class="text-muted">${escapeHtml(fmt)}%${itemsTotal > 0 ? ` · ${escapeHtml(String(itemsDone))}/${escapeHtml(String(itemsTotal))}` : ''} · ${escapeHtml(etaPart)}</small>
  ${fileLine}
</div>`;
}

export function statusBadgeClass(status) {
    switch (status) {
        case 'running':
            return 'bg-warning-subtle text-warning';
        case 'pending':
            return 'bg-secondary-subtle text-secondary';
        case 'delayed':
            return 'bg-info-subtle text-info';
        case 'failed':
        case 'tour_failed':
            return 'bg-danger-subtle text-danger';
        case 'tour_processing':
            return 'bg-primary-subtle text-primary';
        case 'tour_done':
            return 'bg-success-subtle text-success';
        default:
            return 'bg-light text-muted';
    }
}

export function humanizeStatus(status) {
    const m = String(status ?? '').replace(/_/g, ' ');
    return m ? m.charAt(0).toUpperCase() + m.slice(1) : '';
}
