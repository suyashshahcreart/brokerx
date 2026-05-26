/**
 * Compact queue summary on admin dashboard card.
 */

import {
    escapeHtml,
    renderTourZipProgressHtml,
    statusBadgeClass,
    humanizeStatus,
    formatEtaSeconds,
} from '../utils/tour-zip-progress-ui.js';

const POLL_MS = 2000;

function fmtShort(iso) {
    if (!iso) return '—';
    try {
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return '—';
        return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    } catch {
        return '—';
    }
}

function dashEtaCell(row) {
    const p = row.progress;
    if (p && p.tour_zip_status === 'processing' && p.eta_seconds != null) {
        return escapeHtml(formatEtaSeconds(p.eta_seconds));
    }
    if (row.error_summary) {
        return `<span class="text-danger">${escapeHtml(String(row.error_summary).slice(0, 80))}</span>`;
    }
    return '—';
}

function dashActions(row) {
    const l = row.links || {};
    const parts = [];
    if (l.tour_show) {
        parts.push(`<a href="${escapeHtml(l.tour_show)}" class="btn btn-soft-primary btn-sm py-0 px-2">Tour</a>`);
    }
    if (l.booking) {
        parts.push(`<a href="${escapeHtml(l.booking)}" class="btn btn-soft-secondary btn-sm py-0 px-2">Booking</a>`);
    }
    return parts.join(' ');
}

function renderDashRow(row) {
    const badge = statusBadgeClass(row.status);
    const stLabel = humanizeStatus(row.status);
    const prog = row.progress ? renderTourZipProgressHtml(row.progress, { compact: true }) : '—';

    return `<tr>
<td class="small"><code>${escapeHtml(row.queue ?? '')}</code></td>
<td class="small">${escapeHtml(row.job_label ?? '')}</td>
<td><span class="badge ${badge}">${escapeHtml(stLabel)}</span></td>
<td>
  <span class="fw-medium">${escapeHtml(row.subject ?? '')}</span>
  ${row.customer_name ? `<span class="d-block small text-muted">${escapeHtml(row.customer_name)}</span>` : ''}
</td>
<td class="small align-top">${prog}</td>
<td class="small">${dashEtaCell(row)}</td>
<td class="small text-nowrap">${fmtShort(row.started_at ?? row.queued_at)}</td>
<td>${dashActions(row) || '—'}</td>
</tr>`;
}

function initDashboardQueueMonitor() {
    const holder = document.getElementById('dashboard-queue-monitor');
    if (!holder) return;

    const apiUrl = holder.dataset.apiUrl;
    const limit = parseInt(holder.dataset.limit ?? '8', 10) || 8;

    /** @type {NodeListOf<HTMLButtonElement>} */
    const chips = document.querySelectorAll('.qm-dash-chip');
    let chip = 'all';

    chips.forEach((btn) => {
        btn.addEventListener('click', () => {
            chips.forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            chip = btn.dataset.chip || 'all';
            poll();
        });
    });

    async function poll() {
        try {
            const url = `${apiUrl}?dashboard=1&limit=${limit}&chip=${encodeURIComponent(chip)}`;
            const res = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!res.ok) {
                holder.innerHTML = `<div class="text-danger small">Could not load queue (${res.status})</div>`;
                return;
            }
            const data = await res.json();
            const s = data.summary || {};
            const set = (id, v) => {
                const el = document.getElementById(id);
                if (el) el.textContent = v ?? '—';
            };
            set('dash-qm-pending', String(s.pending ?? 0));
            set('dash-qm-running', String(s.running ?? 0));
            set('dash-qm-failed', String(s.failed_24h ?? 0));

            const rows = data.rows ?? [];
            if (rows.length === 0) {
                holder.innerHTML = '<p class="text-muted small mb-0">No queued or running jobs right now.</p>';
                return;
            }

            holder.innerHTML = `<div class="table-responsive"><table class="table table-hover table-centered table-sm mb-0">
<thead class="bg-light-subtle"><tr>
<th>Queue</th><th>Job</th><th>Status</th><th>Subject</th><th>Progress</th><th>ETA</th><th>When</th><th></th>
</tr></thead><tbody>${rows.map(renderDashRow).join('')}</tbody></table></div>`;
        } catch (e) {
            holder.innerHTML = `<div class="text-muted small">${escapeHtml(String(e.message))}</div>`;
        }
    }

    poll();
    setInterval(poll, POLL_MS);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardQueueMonitor);
} else {
    initDashboardQueueMonitor();
}
