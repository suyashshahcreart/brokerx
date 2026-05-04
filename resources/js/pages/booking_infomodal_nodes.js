/* global window, document */

function isNonEmptyString(value) {
  return typeof value === 'string' && value.trim() !== '';
}

function safeArray(value) {
  return Array.isArray(value) ? value : [];
}

function pickLocalizedString(value) {
  if (isNonEmptyString(value)) return value.trim();
  if (!value || typeof value !== 'object') return '';

  // common language keys
  const preferred = ['en', 'gu', 'hi'];
  for (const k of preferred) {
    if (isNonEmptyString(value[k])) return value[k].trim();
  }

  // fallback: first string value in the object
  for (const k of Object.keys(value)) {
    if (isNonEmptyString(value[k])) return value[k].trim();
  }

  return '';
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function tryFindNodesArray(root) {
  // Most common shapes we’ve seen/expect:
  // - finalJson.nodes
  // - finalJson.tour.nodes
  // - finalJson.data.nodes
  if (root && Array.isArray(root.nodes)) return root.nodes;

  // Fallback: shallow scan one level deep for a `nodes` array.
  if (root && typeof root === 'object') {
    for (const key of Object.keys(root)) {
      const value = root[key];
      if (value && Array.isArray(value.nodes)) return value.nodes;
    }
  }
  return [];
}

function normalizeNodes(finalJson) {
  const nodes = tryFindNodesArray(finalJson);

  return nodes
    .filter((n) => n && typeof n === 'object')
    .map((node) => {
      const infoModals = safeArray(node?.infoPoints).length > 0 ? node.infoPoints : [];
      return {
        id: node.id ?? '',
        name: node.name ?? '',
        showInSideMenu: Boolean(node.showInSideMenu),
        sideMenuTitle: pickLocalizedString(node.sideMenuTitle) || '',
        sideMenuOrder:
          typeof node.sideMenuOrder === 'number' ? node.sideMenuOrder : null,
        infoModals: safeArray(infoModals),
      };
    })
    .filter((n) => n.showInSideMenu && n.infoModals.length > 0);
}

function render(nodes, query='') {
  const listEl = document.getElementById('infomodalNodesList');
  const metaEl = document.getElementById('infomodalNodesMeta');
  const emptyEl = document.getElementById('infomodalNodesEmpty');
  if (!listEl || !metaEl || !emptyEl) return;

  const q = String(query ?? '').trim().toLowerCase();
  const filtered = !q
    ? nodes
    : nodes.filter((n) => {
      const hay = [
        n.id,
        n.name,
        n.sideMenuTitle,
        ...n.infoModals.map((m) =>
          pickLocalizedString(m?.title) || pickLocalizedString(m?.infoModalTitle) || '',
        ),
      ]
        .join(' ')
        .toLowerCase();
      return hay.includes(q);
    });

  const modalCount = filtered.reduce((acc, n) => acc + n.infoModals.length, 0);
  metaEl.textContent = `${filtered.length} node(s), ${modalCount} info modal(s)`;

  if (filtered.length === 0) {
    listEl.innerHTML = '';
    emptyEl.classList.remove('d-none');
    return;
  }

  emptyEl.classList.add('d-none');

  const accordionId = 'infomodalNodesAccordion';
  const html = [
    `<div class="accordion" id="${accordionId}">`,
    ...filtered.map((node, idx) => {
      const itemId = `${accordionId}-item-${idx}`;
      const headingId = `${accordionId}-heading-${idx}`;
      const collapseId = `${accordionId}-collapse-${idx}`;

      const title = escapeHtml(pickLocalizedString(node.sideMenuTitle) || node.name || node.id);
      const nodeId = escapeHtml(node.id);
      const badge = `<span class="badge bg-primary-subtle text-primary ms-2">${node.infoModals.length}</span>`;

      const modalsHtml = node.infoModals
        .map((m, mi) => {
          console.log("in loop",m)
          const mt = escapeHtml(
            pickLocalizedString(m?.title) ||
            pickLocalizedString(m?.infoModalTitle) ||
            `Info modal ${mi + 1}`,
          );
          const desc = escapeHtml(
            pickLocalizedString(m?.description) ||
            pickLocalizedString(m?.infoModalDescription) ||
            '',
          );
          const iframe = escapeHtml(m?.iframeUrl || m?.infoModalIframeUrl || '');
          const link = escapeHtml(m?.link || m?.infoModalLink || '');
          const width = escapeHtml(m?.width || m?.infoModalWidth || '');
          const footerBtnTitle = escapeHtml(m?.footerButtonTitle || m?.infoModalFooterButtonTitle || '');
          const footerBtnLink = escapeHtml(m?.footerButtonLink || m?.infoModalFooterButtonLink || '');
          const footerText = escapeHtml(
            pickLocalizedString(m?.footerText) ||
            pickLocalizedString(m?.infoModalFooterText) ||
            '',
          );
          const sourceInfoPointId = escapeHtml(m?._sourceInfoPoint?.id || '');

          return `
            <div class="border rounded p-2 mb-2 bg-light">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="fw-semibold">${mt}</div>
                ${sourceInfoPointId ? `<span class="badge text-bg-secondary">infoPoint: ${sourceInfoPointId}</span>` : ''}
              </div>
              <div class="mt-2 small">
                ${desc ? `<div><span class="text-muted">Description:</span> ${desc}</div>` : ''}
                ${iframe ? `<div class="mt-1"><span class="text-muted">Iframe:</span> <code>${iframe}</code></div>` : ''}
                ${link ? `<div class="mt-1"><span class="text-muted">Link:</span> <code>${link}</code></div>` : ''}
                ${width ? `<div class="mt-1"><span class="text-muted">Width:</span> <code>${width}</code></div>` : ''}
                ${footerText ? `<div class="mt-1"><span class="text-muted">Footer text:</span> ${footerText}</div>` : ''}
                ${footerBtnTitle || footerBtnLink ? `<div class="mt-1"><span class="text-muted">Footer button:</span> ${footerBtnTitle ? `<span class="me-1">${footerBtnTitle}</span>` : ''}${footerBtnLink ? `<code>${footerBtnLink}</code>` : ''}</div>` : ''}
              </div>
            </div>
          `;
        })
        .join('');

      return `
        <div class="accordion-item" id="${itemId}">
          <h2 class="accordion-header" id="${headingId}">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
              <span class="me-2">${title}</span>
              <span class="text-muted small">(${nodeId})</span>
              ${badge}
            </button>
          </h2>
          <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}">
            <div class="accordion-body">
              ${modalsHtml}
            </div>
          </div>
        </div>
      `;
    }),
    `</div>`,
  ].join('');

  listEl.innerHTML = html;

  // Fallback toggle handler (some templates override accordion behavior).
  listEl.querySelectorAll('[data-bs-toggle="collapse"]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      const target = btn.getAttribute('data-bs-target');
      if (!target) return;
      const el = document.querySelector(target);
      const Collapse = window?.bootstrap?.Collapse;
      if (!el || !Collapse) return; // let bootstrap default behavior handle

      // Ensure it can always toggle closed.
      e.preventDefault();
      Collapse.getOrCreateInstance(el, { toggle: false }).toggle();
    });
  });
}

function init() {
  const searchEl = document.getElementById('infomodalNodesSearch');
  const refreshBtn = document.getElementById('infomodalNodesRefreshBtn');
  if (!searchEl || !refreshBtn) return;

  const nodes = normalizeNodes(window.tourFinalJson || {});
  render(nodes, searchEl.value);

  const onChange = () => render(nodes, searchEl.value);
  searchEl.addEventListener('input', onChange);
  refreshBtn.addEventListener('click', () => {
    searchEl.value = '';
    render(nodes, '');
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

