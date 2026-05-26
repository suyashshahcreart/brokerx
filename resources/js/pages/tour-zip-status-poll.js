/**
 * Live polls tour ZIP processing status for #tour-live-link-box.
 * Reads data-status-url, data-initial-status, data-started-at-ms from the box element.
 */

import { PHASE_LABELS, escapeHtml, truncateMiddle, formatEtaSeconds } from '../utils/tour-zip-progress-ui.js';

function showTourZipToast(message, type) {
    try {
        const existing = document.getElementById('tour-status-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'tour-status-toast';
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '1080';
        const bg =
            type === 'success'
                ? 'success'
                : type === 'error'
                  ? 'danger'
                  : 'info';
        toast.innerHTML = `
                <div class="toast align-items-center text-bg-${bg} border-0 show" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">${escapeHtml(message)}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"></button>
                    </div>
                </div>`;
        document.body.appendChild(toast);
        toast.querySelector('.btn-close')?.addEventListener('click', () => toast.remove());
        setTimeout(() => toast.remove(), 6000);
    } catch (e) {
        alert(message);
    }
}

function initTourZipStatusPoll() {
    const box = document.getElementById('tour-live-link-box');
    const content = document.getElementById('tour-live-link-content');
    if (!box || !content) return;

    const statusUrl = box.dataset.statusUrl;
    const initialStatus = box.dataset.initialStatus ?? 'pending';
    const startedMsAttr = box.dataset.startedAtMs;
    let startedAtMs = startedMsAttr && !Number.isNaN(Number(startedMsAttr)) ? Number(startedMsAttr) : null;
    let isProcessing = initialStatus === 'processing';
    let lastStatus = null;

    if (!statusUrl) return;

    function formatElapsed() {
        if (!startedAtMs) return '';
        const diffMs = Math.max(0, Date.now() - startedAtMs);
        const totalSeconds = Math.floor(diffMs / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        if (minutes === 0) return `${seconds}s`;
        return `${minutes}m ${String(seconds).padStart(2, '0')}s`;
    }

    function render(data) {
        const status = data?.tour_zip_status ?? 'pending';
        const rawProgress = Number.parseFloat(String(data?.tour_zip_progress ?? '0'));
        const progress = Math.max(0, Math.min(100, Number.isFinite(rawProgress) ? rawProgress : 0));
        const progressFmt = Number.isFinite(rawProgress)
            ? Math.min(100, Math.max(0, rawProgress)).toFixed(2)
            : progress.toFixed(2);

        const message = data?.tour_zip_message ?? '';
        const phaseKey = data?.tour_zip_phase ?? '';
        const phaseLabel =
            PHASE_LABELS[phaseKey] ||
            (phaseKey ? phaseKey.replace(/_/g, ' ') : '');
        const liveUrl = data?.tour_live_url ?? '#';
        const hasLive = data?.has_live_link === true;
        const etaSec = data?.tour_zip_eta_seconds;
        const itemsDone = data?.tour_zip_items_done ?? 0;
        const itemsTotal = data?.tour_zip_items_total ?? 0;
        const currentItem = data?.tour_zip_current_item ?? '';

        const startedAt = data?.tour_zip_started_at ? new Date(data.tour_zip_started_at) : null;
        isProcessing = status === 'processing';
        if (startedAt && !Number.isNaN(startedAt.getTime())) {
            startedAtMs = startedAt.getTime();
        } else if (status === 'processing' && startedAtMs == null && startedMsAttr) {
            startedAtMs = Number(startedMsAttr);
        }

        if (status === 'processing') {
            const width = Math.max(0.05, Math.min(100, progress));
            const elapsed = formatElapsed();
            const fileLine =
                currentItem ?
                    `<small class="text-muted d-block text-truncate mt-1" title="${escapeHtml(currentItem)}">${escapeHtml(truncateMiddle(currentItem, 60))}</small>`
                : '';

            const countPart =
                itemsTotal > 0 ? `${itemsDone}/${itemsTotal} files · ` : '';
            const etaPart =
                etaSec != null && progress > 1
                    ? formatEtaSeconds(etaSec)
                    : 'Calculating ETA…';

            content.innerHTML = `
                <p class="text-warning mb-1 small">
                    ${phaseLabel ? `<strong>${escapeHtml(phaseLabel)}</strong>` : ''}
                    ${phaseLabel && message ? ' — ' : ''}
                    Processing ZIP… ${message ? '(' + escapeHtml(message) + ')' : ''}
                </p>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated tour-zip-live-progress"
                         role="progressbar"
                         style="width: ${width}%; transition: width 0.4s ease;"
                         aria-valuenow="${progressFmt}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted d-block mt-1">
                    ${progressFmt}% · ${countPart}${etaPart}${elapsed ? ' · running <span id="tour-zip-elapsed">' + escapeHtml(elapsed) + '</span>' : ''}
                </small>
                ${fileLine}`;
            return;
        }

        if (status === 'failed') {
            content.innerHTML = `<p class="text-danger mb-0">Processing failed${message ? ': ' + escapeHtml(message) : '.'}</p>`;
            return;
        }

        if (status === 'done') {
            if (hasLive && liveUrl && liveUrl !== '#') {
                content.innerHTML = `
                    <p class="mb-0">
                        <div class="d-flex align-items-center gap-2">
                            <a href="${escapeHtml(liveUrl)}" target="_blank" rel="noopener" class="text-truncate d-block flex-grow-1" style="max-width: 100%;">
                            ${escapeHtml(liveUrl.length > 40 ? liveUrl.slice(0, 40) + '…' : liveUrl)}
                            </a>
                            <button type="button"
                                class="btn btn-link btn-sm p-0 copy-link-btn"
                                data-copy-text="${escapeHtml(liveUrl)}"
                                title="Copy live link" aria-label="Copy live link">
                                <i class="ri-file-copy-line"></i>
                            </button>
                        </div>
                    </p>`;
            } else {
                content.innerHTML = `<p class="text-muted mb-0">Please upload a ZIP Again to generate the live link.</p>`;
            }
            return;
        }

        if (status === 'pending') {
            content.innerHTML = `<p class="text-muted mb-0">Please upload a ZIP to generate the live link.</p>`;
            return;
        }

        content.innerHTML = `<p class="text-muted mb-0">Please upload a ZIP Again to generate the live link.</p>`;
    }

    const POLL_MS = 2000;

    async function poll() {
        try {
            const res = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) {
                if (lastStatus === 'processing') {
                    console.warn('Status API HTTP', res.status, '- retry 10s');
                    setTimeout(() => poll(), 10000);
                }
                return;
            }

            const data = await res.json();
            const status = data?.tour_zip_status ?? 'pending';

            const wasProcessing = lastStatus === 'processing';
            const isNowDone = status === 'done';
            const isNowFailed = status === 'failed';
            const isNowNotProcessing = status !== 'processing';

            if (wasProcessing && isNowNotProcessing) {
                if (isNowDone && (data?.has_live_link ?? false)) {
                    showTourZipToast('Tour processing completed! Live link is ready.', 'success');
                } else if (isNowFailed) {
                    showTourZipToast('Tour processing failed. Please check logs.', 'error');
                } else {
                    showTourZipToast('Tour processing finished.', 'info');
                }
            }

            lastStatus = status;
            render(data);

            if (wasProcessing && isNowDone) {
                if (!poll._reloaded) {
                    poll._reloaded = true;
                    const url = new URL(window.location.href);
                    url.searchParams.set('completed', '1');
                    setTimeout(() => {
                        window.location.href = url.toString();
                    }, 2000);
                }
                return;
            }

            if (status === 'processing') {
                setTimeout(() => poll(), POLL_MS);
            }
        } catch (e) {
            if (lastStatus === 'processing') {
                setTimeout(() => poll(), 10000);
            }
        }
    }

    setInterval(() => {
        const el = document.getElementById('tour-zip-elapsed');
        if (!el || !isProcessing || !startedAtMs) return;
        const diffMs = Math.max(0, Date.now() - startedAtMs);
        const totalSeconds = Math.floor(diffMs / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        el.textContent = minutes === 0 ? `${seconds}s` : `${minutes}m ${String(seconds).padStart(2, '0')}s`;
    }, 1000);

    if (initialStatus === 'processing') {
        lastStatus = 'processing';
        poll();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTourZipStatusPoll);
} else {
    initTourZipStatusPoll();
}
