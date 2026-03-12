// handle dynamic social links as associative key/value pairs
(function() {
    function createRow(key = '', value = '') {
        const row = document.createElement('div');
        row.className = 'input-group mb-2 social-pair';
        row.innerHTML = `
            <input type="text" class="form-control key-input" placeholder="Key" value="${key}">
            <input type="text" class="form-control value-input" placeholder="Value/URL" value="${value}">
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
        container.querySelectorAll('input[type=hidden].social-hidden').forEach(i => i.remove());
        let hasAny = false;
        container.querySelectorAll('.social-pair').forEach(p => {
            const key = p.querySelector('.key-input').value.trim();
            const val = p.querySelector('.value-input').value.trim();
            if (key) {
                hasAny = true;
                const hid = document.createElement('input');
                hid.type = 'hidden';
                hid.name = `social_link[${key}]`;
                hid.value = val;
                hid.className = 'social-hidden';
                container.appendChild(hid);
            }
        });
        if (!hasAny) {
            // send explicit empty array so backend updates to []
            const hid = document.createElement('input');
            hid.type = 'hidden';
            hid.name = 'social_link';
            hid.value = '[]';
            hid.className = 'social-hidden';
            container.appendChild(hid);
        }
    }

    function init() {
        const container = document.getElementById('social-links-container');
        if (!container) return;

        // data-links should be an object; convert to key/value rows
        let data = {};
        try {
            const raw = container.dataset.links || '{}';
            data = JSON.parse(raw);
            if (Array.isArray(data)) {
                // legacy array-of-pairs support
                const arr = data;
                data = {};
                arr.forEach(item => {
                    if (item && typeof item === 'object') {
                        const k = item.platform || item.key || '';
                        const v = item.url || item.value || '';
                        if (k) data[k] = v;
                    }
                });
            }
        } catch (e) {
            data = {};
        }

        Object.entries(data).forEach(([k, v]) => container.appendChild(createRow(k, v)));

        document.getElementById('add-social-pair').addEventListener('click', () => {
            container.appendChild(createRow());
        });

        document.querySelectorAll('form.needs-validation').forEach(form => {
            form.addEventListener('submit', syncHidden);
        });
    }

    document.addEventListener('DOMContentLoaded', init);
})();