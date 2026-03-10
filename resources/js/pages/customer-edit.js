// handle dynamic social links as array of platform/url pairs
(function() {
    function createRow(platform = '', url = '') {
        const row = document.createElement('div');
        row.className = 'input-group mb-2 social-pair';
        row.innerHTML = `
            <input type="text" class="form-control platform-input" placeholder="Platform (e.g, Twitter)" value="${platform}">
            <input type="url" class="form-control url-input" placeholder="URL (e.g, https://twitter.com/username)" value="${url}">
            <button class="btn btn-outline-danger remove-btn" type="button"><i class="ri-delete-bin-line"></i></button>
        `;
        row.querySelector('.remove-btn').addEventListener('click', () => {
            row.remove();
            syncHidden();
        });
        row.querySelectorAll('input').forEach(inp => inp.addEventListener('input', syncHidden));
        return row;
    }

    function syncHidden() {
        const container = document.getElementById('social-links-container');
        if (!container) return;
        // clear previous hidden inputs
        container.querySelectorAll('input[type=hidden].social-hidden').forEach(i => i.remove());
        container.querySelectorAll('.social-pair').forEach((p, idx) => {
            const plat = p.querySelector('.platform-input').value.trim();
            const url = p.querySelector('.url-input').value.trim();
            if (plat || url) {
                const hidPlat = document.createElement('input');
                hidPlat.type = 'hidden';
                hidPlat.name = `social_link[${idx}][platform]`;
                hidPlat.value = plat;
                hidPlat.className = 'social-hidden';
                container.appendChild(hidPlat);

                const hidUrl = document.createElement('input');
                hidUrl.type = 'hidden';
                hidUrl.name = `social_link[${idx}][url]`;
                hidUrl.value = url;
                hidUrl.className = 'social-hidden';
                container.appendChild(hidUrl);
            }
        });
    }

    function init() {
        const container = document.getElementById('social-links-container');
        if (!container) return;

        // read initial data from data-links attribute (expects JSON array or object)
        let data = [];
        try {
            const raw = container.dataset.links || '[]';
            data = JSON.parse(raw);
            if (data && typeof data === 'object' && !Array.isArray(data)) {
                // convert associative object to array of pairs
                data = Object.entries(data).map(([platform, url]) => ({ platform, url }));
            }
        } catch (e) {
            data = [];
        }

        data.forEach(item => container.appendChild(createRow(item.platform || '', item.url || '')));

        document.getElementById('add-social-pair').addEventListener('click', () => {
            container.appendChild(createRow());
        });

        // keep hidden inputs in sync when form submitted or fields change
        document.querySelectorAll('form.needs-validation').forEach(form => {
            form.addEventListener('submit', syncHidden);
        });
    }

    document.addEventListener('DOMContentLoaded', init);
})();