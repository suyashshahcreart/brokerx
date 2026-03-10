/**
 * Portfolio Data Service - JSON file (no database)
 * Fetches from data.php which reads portfolio.json
 */

console.log('Portfolio script loaded');

const API_CONFIG = {
    baseUrl: 'data.php'
};

let propertyTypeFilters = { property_types: [], other_sub_types: [] };
let filterIdMap = {};

const PortfolioAPI = {
    async getPropertyTypeFilters() {
        try {
            const response = await fetch(`${API_CONFIG.baseUrl}?action=filters`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Failed to fetch filters');
            return data.data;
        } catch (error) {
            console.error('Get filters error:', error);
            throw error;
        }
    },

    async getPortfolioList(filters = null, page = null, perPage = 6, sort = 'sr_no', sortOrder = 'desc') {
        try {
            const params = new URLSearchParams();
            if (filters && filters.length > 0) {
                params.append('property_type_filter', filters.join(','));
            }
            if (page !== null) {
                params.append('page', page);
                params.append('per_page', perPage);
            }
            params.append('sort', sort);
            params.append('sort_order', sortOrder);
            const response = await fetch(`${API_CONFIG.baseUrl}?${params}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Failed to fetch portfolio');
            return data;
        } catch (error) {
            console.error('Get portfolio list error:', error);
            throw error;
        }
    }
};

const FilterManager = {
    buildFilterIdMap(filtersData) {
        filterIdMap = { 'all': null };
        (filtersData.property_types || []).forEach(type => {
            const category = this.getCategoryFromName(type.name);
            if (category && category !== 'all') {
                if (!filterIdMap[category]) filterIdMap[category] = [];
                filterIdMap[category].push(type.name);
            }
        });
        (filtersData.other_sub_types || []).forEach(subType => {
            const category = this.getCategoryFromName(subType.name);
            if (category && category !== 'all') {
                if (!filterIdMap[category]) filterIdMap[category] = [];
                filterIdMap[category].push(subType.name);
            }
        });
    },

    getCategoryFromName(name) {
        const mapping = {
            'Residential': 'residential', 'Commercial': 'commercial',
            'Hospitality': 'hospitality', 'Industries': 'industry', 'Religious': 'religious',
            'Spaces': 'government', 'Heritage': 'protect', 'Healthcare': 'health'
        };
        return mapping[name] || (name ? name.toLowerCase().replace(/\s+/g, '_') : null);
    },

    getFilterIds(category) {
        return filterIdMap[category] || null;
    },

    getCategoryFromData(propertyType, propertySubType) {
        if (propertySubType) {
            const c = this.getCategoryFromName(propertySubType);
            if (c) return c;
        }
        if (propertyType) {
            const c = this.getCategoryFromName(propertyType);
            if (c) return c;
        }
        return 'all';
    }
};

const PortfolioRenderer = {
    renderFilters(filtersData) {
        const container = document.getElementById('portfolioFilters');
        if (!container) return;
        container.innerHTML = '';
        const currentFilter = FilterHandler.currentFilter || 'all';

        const allBtn = document.createElement('button');
        allBtn.className = `filter-pill ${currentFilter === 'all' ? 'active' : ''}`;
        allBtn.setAttribute('data-filter', 'all');
        allBtn.textContent = 'All';
        allBtn.addEventListener('click', () => FilterHandler.handleFilterClick('all'));
        container.appendChild(allBtn);

        const addBtn = (type, name) => {
            const category = FilterManager.getCategoryFromName(name);
            if (category && category !== 'all') {
                const btn = document.createElement('button');
                btn.className = `filter-pill ${currentFilter === category ? 'active' : ''}`;
                btn.setAttribute('data-filter', category);
                btn.textContent = name;
                btn.addEventListener('click', () => FilterHandler.handleFilterClick(category));
                container.appendChild(btn);
            }
        };
        (filtersData.property_types || []).forEach(t => addBtn(t, t.name));
        (filtersData.other_sub_types || []).forEach(t => addBtn(t, t.name));

        FilterManager.buildFilterIdMap(filtersData);
    },

    renderPortfolio(data) {
        const container = document.getElementById('portfolioGrid');
        const loadingEl = document.getElementById('portfolioLoading');
        const errorEl = document.getElementById('portfolioError');
        const emptyEl = document.getElementById('portfolioEmpty');

        if (!container) return;
        if (loadingEl) loadingEl.classList.add('d-none');
        if (errorEl) errorEl.classList.add('d-none');
        if (emptyEl) emptyEl.classList.add('d-none');
        container.innerHTML = '';

        if (!data || !data.data || data.data.length === 0) {
            if (emptyEl) emptyEl.classList.remove('d-none');
            return;
        }

        data.data.forEach(item => {
            const category = FilterManager.getCategoryFromData(item.property_type, item.property_sub_type);
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 portfolio-item';
            col.setAttribute('data-category', category);

            const thumbnail = item.tour?.tour_thumbnail || 'assets/images/mockup.png';
            const title = item.tour?.title || item.tour?.name || 'Untitled Tour';
            const subType = item.property_sub_type || item.property_type || '';
            const tourLiveLink = item.tour?.tour_live_link || item.booking_live_link || item.tour?.hosted_link || '#';
            const srNo = item.sr_no ?? '';

            col.innerHTML = `
                <div class="panorama-item position-relative overflow-hidden rounded shadow-sm h-100">
                    ${srNo ? `<span class="position-absolute top-0 start-0 m-2 badge bg-primary d-none">SR-${srNo}</span>` : ''}
                    <img src="${thumbnail}" alt="${title}" class="panorama-image img-fluid w-100" 
                         onerror="this.src='assets/images/mockup.png'">
                    <div class="panorama-overlay position-absolute bottom-0 start-0 end-0 p-4 text-white">
                        <h4 class="h5 mb-2 fw-bold">${this.escapeHtml(title)}</h4>
                        <p class="mb-0 small opacity-75">${this.escapeHtml(subType)}</p>
                    </div>
                    <a href="${tourLiveLink}" target="_blank" rel="noopener noreferrer" class="stretched-link"></a>
                </div>
            `;

            container.appendChild(col);
        });
    },

    renderPagination(meta, links) {
        const container = document.getElementById('portfolioPagination');
        if (!container || !meta) return;
        container.innerHTML = '';

        if (!meta.total_pages || meta.total_pages <= 1) {
            container.style.display = 'none';
            return;
        }
        container.style.display = 'flex';

        const currentPage = meta.current_page || 1;
        const totalPages = meta.total_pages || 1;

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
        if (currentPage > 1) {
            prevLi.addEventListener('click', (e) => { e.preventDefault(); PaginationHandler.handlePageChange(currentPage - 1); });
        }
        container.appendChild(prevLi);

        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', (e) => { e.preventDefault(); PaginationHandler.handlePageChange(i); });
            container.appendChild(li);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
        if (currentPage < totalPages) {
            nextLi.addEventListener('click', (e) => { e.preventDefault(); PaginationHandler.handlePageChange(currentPage + 1); });
        }
        container.appendChild(nextLi);
    },

    showLoading() {
        const loadingEl = document.getElementById('portfolioLoading');
        const errorEl = document.getElementById('portfolioError');
        const emptyEl = document.getElementById('portfolioEmpty');
        const gridEl = document.getElementById('portfolioGrid');
        if (loadingEl) loadingEl.classList.remove('d-none');
        if (errorEl) errorEl.classList.add('d-none');
        if (emptyEl) emptyEl.classList.add('d-none');
        if (gridEl) gridEl.innerHTML = '';
    },

    showError(message) {
        const loadingEl = document.getElementById('portfolioLoading');
        const errorEl = document.getElementById('portfolioError');
        const errorMsgEl = document.getElementById('portfolioErrorMsg');
        const emptyEl = document.getElementById('portfolioEmpty');
        if (loadingEl) loadingEl.classList.add('d-none');
        if (errorEl) errorEl.classList.remove('d-none');
        if (emptyEl) emptyEl.classList.add('d-none');
        if (errorMsgEl) errorMsgEl.textContent = message;
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

const FilterHandler = {
    currentFilter: 'all',
    currentPage: 1,

    async handleFilterClick(category) {
        document.querySelectorAll('#portfolioFilters .filter-pill').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector(`#portfolioFilters .filter-pill[data-filter="${category}"]`);
        if (activeBtn) activeBtn.classList.add('active');
        this.currentFilter = category;
        this.currentPage = 1;
        URLManager.updateAll();
        await PortfolioLoader.loadPortfolio();
    }
};

const PaginationHandler = {
    async handlePageChange(page) {
        FilterHandler.currentPage = page;
        URLManager.updateAll();
        await PortfolioLoader.loadPortfolio();
        window.scrollTo({ top: document.getElementById('portfolio').offsetTop - 100, behavior: 'smooth' });
    }
};

const PortfolioLoader = {
    async loadPortfolio() {
        PortfolioRenderer.showLoading();
        try {
            const filterIds = FilterManager.getFilterIds(FilterHandler.currentFilter);
            const sort = document.getElementById('portfolioSort')?.value || 'sr_no';
            const sortOrder = document.getElementById('portfolioSortOrder')?.value || 'asc';
            const perPage = parseInt(document.getElementById('portfolioPerPage')?.value || 6) || 6;
            const data = await PortfolioAPI.getPortfolioList(
                filterIds,
                FilterHandler.currentPage,
                perPage,
                sort,
                sortOrder
            );
            PortfolioRenderer.renderPortfolio(data);
            if (data.meta) PortfolioRenderer.renderPagination(data.meta, data.links);
        } catch (error) {
            console.error('Load portfolio error:', error);
            PortfolioRenderer.showError(error.message || 'Failed to load portfolio. Please try again.');
        }
    },

    async loadFilters() {
        try {
            const filtersData = await PortfolioAPI.getPropertyTypeFilters();
            propertyTypeFilters = filtersData;
            PortfolioRenderer.renderFilters(filtersData);
        } catch (error) {
            console.error('Load filters error:', error);
        }
    }
};

const URLManager = {
    getFilterFromURL() {
        return new URLSearchParams(window.location.search).get('filter') || 'all';
    },
    getPageFromURL() {
        const p = parseInt(new URLSearchParams(window.location.search).get('page')) || 1;
        return p > 0 ? p : 1;
    },
    getSortFromURL() {
        return new URLSearchParams(window.location.search).get('sort') || 'sr_no';
    },
    getSortOrderFromURL() {
        return new URLSearchParams(window.location.search).get('sort_order') || 'asc';
    },
    getPerPageFromURL() {
        const p = parseInt(new URLSearchParams(window.location.search).get('per_page')) || 6;
        return [6, 9, 12, 24].includes(p) ? p : 6;
    },
    updateAll() {
        const params = new URLSearchParams(window.location.search);
        params.set('filter', FilterHandler.currentFilter);
        params.set('page', FilterHandler.currentPage);
        params.set('sort', document.getElementById('portfolioSort')?.value || 'sr_no');
        params.set('sort_order', document.getElementById('portfolioSortOrder')?.value || 'asc');
        params.set('per_page', document.getElementById('portfolioPerPage')?.value || 6);
        if (params.get('filter') === 'all') params.delete('filter');
        if (params.get('page') === '1') params.delete('page');
        if (params.get('sort') === 'sr_no') params.delete('sort');
        if (params.get('sort_order') === 'asc') params.delete('sort_order');
        if (params.get('per_page') === '6') params.delete('per_page');
        window.history.pushState({}, '', window.location.pathname + (params.toString() ? '?' + params.toString() : ''));
    }
};

const PortfolioApp = {
    async init() {
        FilterHandler.currentFilter = URLManager.getFilterFromURL();
        FilterHandler.currentPage = URLManager.getPageFromURL();

        const sortEl = document.getElementById('portfolioSort');
        const sortOrderEl = document.getElementById('portfolioSortOrder');
        const perPageEl = document.getElementById('portfolioPerPage');
        if (sortEl) sortEl.value = URLManager.getSortFromURL();
        if (sortOrderEl) sortOrderEl.value = URLManager.getSortOrderFromURL();
        if (perPageEl) perPageEl.value = URLManager.getPerPageFromURL();

        const onControlChange = () => {
            FilterHandler.currentPage = 1;
            URLManager.updateAll();
            PortfolioLoader.loadPortfolio();
        };
        if (sortEl) sortEl.addEventListener('change', onControlChange);
        if (sortOrderEl) sortOrderEl.addEventListener('change', onControlChange);
        if (perPageEl) perPageEl.addEventListener('change', onControlChange);

        try {
            await PortfolioLoader.loadFilters();
            await PortfolioLoader.loadPortfolio();
        } catch (error) {
            console.error('Portfolio app init error:', error);
        }
    }
};

function initializePortfolioApp() {
    if (typeof bootstrap === 'undefined') {
        setTimeout(initializePortfolioApp, 100);
        return;
    }
    PortfolioApp.init();

    const clearBtn = document.getElementById('portfolioClear');
    if (clearBtn) clearBtn.addEventListener('click', () => FilterHandler.handleFilterClick('all'));

    const retryBtn = document.getElementById('portfolioRetryBtn');
    if (retryBtn) retryBtn.addEventListener('click', () => PortfolioApp.init());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePortfolioApp);
} else {
    initializePortfolioApp();
}
