/**
 * Full-page queue monitor: filters, pagination, 2s auto-refresh.
 */

import {
    escapeHtml,
    renderTourZipProgressHtml,
    statusBadgeClass,
    humanizeStatus,
    formatEtaSeconds,
} from '../utils/tour-zip-progress-ui.js';

const POLL_MS = 2000;

function fmtIso(iso) {
    if (!iso) return '—';
    try {
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return '—';
        return d.toLocaleString();
    } catch {
        return '—';
    }
}

function etaNote(row) {
    const p = row.progress;
    if (p && p.tour_zip_status === 'processing' && p.eta_seconds != null && row.started_at) {
        const start = new Date(row.started_at).getTime();
        if (!Number.isNaN(start)) {
            const end = new Date(start + Math.max(0, p.eta_seconds) * 1000);
            return `${formatEtaSeconds(p.eta_seconds)}<br><span class="text-muted">~${end.toLocaleTimeString()}</span>`;
        }
    }
    if (row.error_summary) {
        return escapeHtml(row.error_summary);
    }
    return '—';
}

function actionsHtml(row) {
    const l = row.links || {};
    const parts = [];
    if (l.booking) {
        parts.push(`<a href="${escapeHtml(l.booking)}" class="btn btn-soft-primary btn-sm">Booking</a>`);
    }
    if (l.tour_show) {
        parts.push(`<a href="${escapeHtml(l.tour_show)}" class="btn btn-soft-info btn-sm">Tour</a>`);
    }
    if (l.tour_upload) {
        parts.push(`<a href="${escapeHtml(l.tour_upload)}" class="btn btn-soft-warning btn-sm">Upload</a>`);
    }
    return parts.length ? `<div class="d-flex flex-wrap gap-1 justify-content-end">${parts.join('')}</div>` : '—';
}

function renderRow(row) {
    const badge = statusBadgeClass(row.status);
    const stLabel = humanizeStatus(row.status);
    const progressHtml = row.progress ? renderTourZipProgressHtml(row.progress, { compact: false }) : '<span class="text-muted">—</span>';
    const subj = escapeHtml(row.subject ?? '');
    const cust = row.customer_name ? `<div class="small text-muted">${escapeHtml(row.customer_name)}</div>` : '';

    return `<tr>
<td><code class="small">${escapeHtml(row.queue ?? '')}</code></td>
<td><span class="small">${escapeHtml(row.job_label ?? row.job_class ?? '')}</span></td>
<td><span class="badge ${badge}">${escapeHtml(stLabel)}</span></td>
<td><div class="fw-medium">${subj}</div>${cust}</td>
<td class="align-top">${progressHtml}</td>
<td class="small">${fmtIso(row.queued_at)}</td>
<td class="small">${fmtIso(row.started_at)}</td>
<td class="small">${etaNote(row)}</td>
<td class="small">${fmtIso(row.finished_at)}</td>
<td class="text-end">${actionsHtml(row)}</td>
</tr>`;
}

function initQueueMonitorPage() {
    const root = document.getElementById('queue-monitor-root');
    if (!root) return;

    const apiUrl = root.dataset.apiUrl;
    const filtersUrl = root.dataset.filtersUrl;
    const tbody = document.getElementById('queue-monitor-tbody');
    const form = document.getElementById('queue-monitor-form');
    const autoEl = document.getElementById('qm-auto-refresh');
    const infoEl = document.getElementById('qm-pagination-info');
    const prevBtn = document.getElementById('qm-page-prev');
    const nextBtn = document.getElementById('qm-page-next');

    let page = 1;
    const perPage = 25;
    let pollTimer = null;
    let lastLastPage = 1;

    function updateSummary(s) {
        const el = (id) => document.getElementById(id);
        if (!s) return;
        el('qm-sum-pending').textContent = s.pending ?? '0';
        el('qm-sum-running').textContent = s.running ?? '0';
        el('qm-sum-delayed').textContent = s.delayed ?? '0';
        el('qm-sum-failed').textContent = s.failed_24h ?? '0';
        el('qm-sum-tours').textContent = s.tours_processing ?? '0';
    }

    async function loadFiltersOnce() {
        if (!filtersUrl) return;
        try {
            const res = await fetch(filtersUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            const qSel = document.getElementById('qm-filter-queue');
            const jSel = document.getElementById('qm-filter-job-class');
            if (qSel && Array.isArray(data.queues)) {
                const cur = qSel.value;
                qSel.innerHTML = '<option value="all">All</option>';
                data.queues.forEach((q) => {
                    const o = document.createElement('option');
                    o.value = q;
                    o.textContent = q;
                    qSel.appendChild(o);
                });
                qSel.value = cur;
            }
            if (jSel && Array.isArray(data.job_classes)) {
                const curj = jSel.value;
                jSel.innerHTML = '<option value="all">All</option>';
                data.job_classes.forEach((jc) => {
                    const o = document.createElement('option');
                    o.value = jc;
                    o.textContent = jc.split('\\').pop() || jc;
                    jSel.appendChild(o);
                });
                jSel.value = curj;
            }
        } catch {
            /* ignore */
        }
    }

    async function fetchData() {
        const fd = new FormData(form);
        const params = new URLSearchParams();
        params.set('page', String(page));
        params.set('per_page', String(perPage));
        for (const [k, v] of fd.entries()) {
            if (v !== '' && v != null) params.set(k, String(v));
        }

        const url = `${apiUrl}?${params.toString()}`;
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!res.ok) {
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">HTTP ${res.status}</td></tr>`;
            return;
        }
        const data = await res.json();
        updateSummary(data.summary);

        const rows = data.rows ?? [];
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No jobs match filters.</td></tr>';
        } else {
            tbody.innerHTML = rows.map(renderRow).join('');
        }

        const pag = data.pagination ?? {};
        lastLastPage = pag.last_page ?? 1;
        const total = pag.total ?? 0;
        infoEl.textContent = `Page ${pag.current_page ?? page} of ${lastLastPage} · ${total} total`;
        prevBtn.disabled = page <= 1;
        nextBtn.disabled = page >= lastLastPage;
    }

    function schedulePoll() {
        if (pollTimer) clearTimeout(pollTimer);
        if (!autoEl?.checked) return;
        pollTimer = setTimeout(async () => {
            await fetchData();
            schedulePoll();
        }, POLL_MS);
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        page = 1;
        fetchData().then(schedulePoll);
    });

    prevBtn.addEventListener('click', () => {
        if (page > 1) page -= 1;
        fetchData().then(schedulePoll);
    });
    nextBtn.addEventListener('click', () => {
        if (page < lastLastPage) page += 1;
        fetchData().then(schedulePoll);
    });

    autoEl.addEventListener('change', () => {
        if (pollTimer) clearTimeout(pollTimer);
        if (autoEl.checked) schedulePoll();
    });

    loadFiltersOnce();
    fetchData().then(schedulePoll);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQueueMonitorPage);
} else {
    initQueueMonitorPage();
}
