/**
 * Collapse filter panels on mobile; show a toggle button (≤767px).
 */
export function initMobileFiltersToggle(options = {}) {
    const {
        sectionSelector = '#filtersSection',
        toggleSelector = '#toggleMobileFilters',
        labelSelector = '#toggleMobileFiltersLabel',
        onExpand = null,
        breakpoint = '(max-width: 767.98px)',
    } = options;

    const section = document.querySelector(sectionSelector);
    const toggleBtn = document.querySelector(toggleSelector);
    const labelEl = document.querySelector(labelSelector);

    if (!section || !toggleBtn) {
        return;
    }

    const mediaQuery = window.matchMedia(breakpoint);

    function isMobileView() {
        return mediaQuery.matches;
    }

    function updateLabel(expanded) {
        if (labelEl) {
            labelEl.textContent = expanded ? 'Hide Filters' : 'Show Filters';
        }
    }

    function setExpanded(expanded, { syncToggle = true } = {}) {
        if (!isMobileView()) {
            section.classList.add('filters-expanded');
            if (syncToggle) {
                toggleBtn.setAttribute('aria-expanded', 'true');
                updateLabel(true);
            }
            return;
        }

        section.classList.toggle('filters-expanded', expanded);

        if (syncToggle) {
            toggleBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            updateLabel(expanded);
        }

        if (expanded && typeof onExpand === 'function') {
            onExpand();
        }
    }

    setExpanded(false);

    toggleBtn.addEventListener('click', function () {
        setExpanded(!section.classList.contains('filters-expanded'));
    });

    mediaQuery.addEventListener('change', function () {
        setExpanded(!isMobileView());
    });
}
