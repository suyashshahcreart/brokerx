// css Import; CSS for Settings page
import '../../css/pages/setting-index.css';

// Settings page JS — consolidated from Blade inline script
(function () {
    'use strict';

    // Utility: get the main API endpoint from a form with data-csrf
    function getApiAction() {
        return document.querySelector('form[data-csrf]')?.action || '';
    }

    function getActiveTab() {
        const activeTab = document.querySelector('#vl-pills-tab .nav-link.active');
        if (activeTab) {
            const href = activeTab.getAttribute('href');
            return href || activeTab.id;
        }
        return null;
    }

    function setActiveTab(tabSelector) {
        if (!tabSelector) return;
        const tabTrigger = document.querySelector(`#vl-pills-tab .nav-link[href="${tabSelector}"]`);
        if (tabTrigger && typeof bootstrap !== 'undefined') {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        } else if (tabTrigger) {
            document.querySelectorAll('#vl-pills-tab .nav-link').forEach(tab => {
                tab.classList.remove('active', 'show');
                tab.setAttribute('aria-selected', 'false');
            });
            document.querySelectorAll('#vl-pills-tabContent .tab-pane').forEach(pane => {
                pane.classList.remove('active', 'show');
            });
            tabTrigger.classList.add('active', 'show');
            tabTrigger.setAttribute('aria-selected', 'true');
            const targetPane = document.querySelector(tabSelector);
            if (targetPane) {
                targetPane.classList.add('active', 'show');
            }
        }
    }

    function handleFormSubmit(form, submitBtn) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            const activeTab = getActiveTab();
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Updating...';

            const formData = new FormData(form);
            // Handle checkboxes explicitly for known forms
            if (form.id === 'cashfreeForm' || form.id === 'payuForm' || form.id === 'razorpayForm') {
                const checkboxIdMap = {
                    'cashfreeForm': 'cashfree_status',
                    'payuForm': 'payu_status',
                    'razorpayForm': 'razorpay_status'
                };
                const checkboxId = checkboxIdMap[form.id];
                const checkbox = document.getElementById(checkboxId);
                if (checkbox && !checkbox.checked) {
                    formData.set(checkboxId, '0');
                }
            }

            if (!formData.has('_token')) {
                const token = form.getAttribute('data-csrf');
                if (token) formData.append('_token', token);
            }

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                encription: 'multipart/form-data',
                credentials: 'same-origin'
            })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    let data;
                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        throw {
                            status: response.status,
                            data: { message: text || 'An error occurred' }
                        };
                    }
                    if (!response.ok) throw { status: response.status, data };
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: (form.dataset.message || 'Settings updated successfully'),
                                timer: 2000,
                                showConfirmButton: false,
                                timerProgressBar: true
                            });
                        }
                        if (activeTab) localStorage.setItem('settingsActiveTab', activeTab);
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        throw { data };
                    }
                })
                .catch(error => {
                    let errorMessage = 'An error occurred while updating settings.';
                    if (error instanceof TypeError && error.message.includes('fetch')) {
                        errorMessage = 'Network error. Please check your internet connection and try again.';
                    } else if (error.data) {
                        if (error.data.message) {
                            errorMessage = error.data.message;
                        } else if (error.data.errors) {
                            const errors = Object.values(error.data.errors).flat();
                            errorMessage = errors.join('<br>');
                        } else if (error.status === 422) {
                            errorMessage = 'Validation error. Please check your input.';
                        }
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error!', html: errorMessage, confirmButtonText: 'OK' });
                    } else {
                        alert(errorMessage);
                    }
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
        });
    }

    function initPanelCardActions() {
        const newHandler = function (event) {
            const button = event.target.closest('[data-panel-action]');
            if (!button) return;
            const card = button.closest('[data-panel-card]');
            if (!card) return;
            if (!card.closest('.page-content')) return;
            const action = button.getAttribute('data-panel-action');
            if (['collapse', 'fullscreen', 'close'].indexOf(action) === -1) return;
            event.stopImmediatePropagation();
            event.preventDefault();
            switch (action) {
                case 'collapse': handleCollapse(card, button); break;
                case 'fullscreen': handleFullscreen(card, button); break;
                case 'close': handleClose(card); break;
            }
        };
        document.addEventListener('click', newHandler, true);
    }

    function handleCollapse(card, button) {
        const sections = card.querySelectorAll('.card-body, .card-footer');
        if (!sections.length) return;
        const isCollapsed = sections[0].classList.contains('d-none');
        sections.forEach(section => {
            if (isCollapsed) { section.classList.remove('d-none'); section.style.display = ''; }
            else { section.classList.add('d-none'); section.style.display = 'none'; }
        });
        const icon = button.querySelector('i');
        if (icon) {
            if (isCollapsed) { icon.classList.remove('ri-arrow-down-s-line'); icon.classList.add('ri-arrow-up-s-line'); }
            else { icon.classList.remove('ri-arrow-up-s-line'); icon.classList.add('ri-arrow-down-s-line'); }
        }
    }

    function handleFullscreen(card, button) {
        const isFullscreen = card.classList.contains('card-fullscreen') || card.classList.contains('panel-card-fullscreen');
        if (isFullscreen) { card.classList.remove('card-fullscreen', 'panel-card-fullscreen'); document.body.style.overflow = ''; }
        else { card.classList.add('card-fullscreen'); document.body.style.overflow = 'hidden'; }
        const icon = button.querySelector('i');
        if (icon) {
            if (isFullscreen) { icon.classList.remove('ri-fullscreen-exit-line'); icon.classList.add('ri-fullscreen-line'); }
            else { icon.classList.remove('ri-fullscreen-line'); icon.classList.add('ri-fullscreen-exit-line'); }
        }
    }

    function handleClose(card) {
        if (confirm('Are you sure you want to close this card?')) {
            card.style.transition = 'opacity 0.3s ease';
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 300);
        }
    }

    function saveActivePaymentGateway() {
        const cashfreeForm = document.getElementById('cashfreeForm');
        const csrfToken = cashfreeForm ? cashfreeForm.getAttribute('data-csrf') : '';
        const cashfreeStatus = document.getElementById('cashfree_status')?.checked || false;
        const payuStatus = document.getElementById('payu_status')?.checked || false;
        const razorpayStatus = document.getElementById('razorpay_status')?.checked || false;
        const activeGateways = [];
        if (cashfreeStatus) activeGateways.push('Cashfree');
        if (payuStatus) activeGateways.push('PayU Money');
        if (razorpayStatus) activeGateways.push('Razorpay');
        const activeGatewayValue = activeGateways.map(g => g.toLowerCase().replace(' money', '').replace(' ', '')).join(',');
        const activeGatewayDisplay = activeGateways.length > 0 ? activeGateways.join(', ') : 'None';
        const formData = new FormData();
        formData.append('active_payment_gateway', activeGatewayValue);
        formData.append('cashfree_status', cashfreeStatus ? '1' : '0');
        formData.append('payu_status', payuStatus ? '1' : '0');
        formData.append('razorpay_status', razorpayStatus ? '1' : '0');
        if (csrfToken) formData.append('_token', csrfToken);
        const action = getApiAction();
        fetch(action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                let data;
                if (contentType && contentType.includes('application/json')) data = await response.json();
                else { const text = await response.text(); throw { status: response.status, data: { message: text || 'An error occurred' } }; }
                if (!response.ok) throw { status: response.status, data };
                return data;
            })
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Updated!', text: `Active payment gateway${activeGateways.length > 1 ? 's' : ''}: ${activeGatewayDisplay}`, timer: 2000, showConfirmButton: false, timerProgressBar: true, toast: true, position: 'top-end' });
                    }
                } else throw { data };
            })
            .catch(error => {
                let errorMessage = 'Failed to update active payment gateway.';
                if (error instanceof TypeError && error.message.includes('fetch')) errorMessage = 'Network error. Please check your internet connection.';
                else if (error.data) {
                    if (error.data.message) errorMessage = error.data.message;
                    else if (error.data.errors) { const errors = Object.values(error.data.errors).flat(); errorMessage = errors.join('<br>'); }
                    else if (error.status === 500) errorMessage = 'Server error. Please try again later.';
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error!', html: errorMessage, timer: 3000, showConfirmButton: false, timerProgressBar: true, toast: true, position: 'top-end' });
                } else alert(errorMessage);
            });
    }

    function initTemplateManagement() {
        const msg91TemplatesModal = document.getElementById('msg91TemplatesModal');
        const templatesTableBody = document.getElementById('templatesTableBody');
        if (!templatesTableBody) return;

        function attachDeleteHandler(btn) {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                if (confirm('Are you sure you want to delete this template?')) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
            });
        }

        const addTemplateBtn = document.getElementById('addTemplateBtn');
        const saveTemplatesBtn = document.getElementById('saveTemplatesBtn');

        if (addTemplateBtn) {
            const newAddBtn = addTemplateBtn.cloneNode(true);
            addTemplateBtn.parentNode.replaceChild(newAddBtn, addTemplateBtn);
            newAddBtn.addEventListener('click', function () {
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>
                        <input type="text" 
                            class="form-control form-control-sm template-key" 
                            value="" 
                            data-original=""
                            placeholder="e.g., login_otp">
                    </td>
                    <td>
                        <input type="text" 
                            class="form-control form-control-sm template-id" 
                            value="" 
                            placeholder="Template ID">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger delete-template-btn">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                `;
                templatesTableBody.appendChild(newRow);
                attachDeleteHandler(newRow.querySelector('.delete-template-btn'));
                newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                newRow.querySelector('.template-key').focus();
            });
        }

        templatesTableBody.querySelectorAll('.delete-template-btn').forEach(btn => attachDeleteHandler(btn));

        if (saveTemplatesBtn) {
            const newSaveBtn = saveTemplatesBtn.cloneNode(true);
            saveTemplatesBtn.parentNode.replaceChild(newSaveBtn, saveTemplatesBtn);
            newSaveBtn.addEventListener('click', function () { saveTemplates(); });
        }

        if (msg91TemplatesModal) {
            msg91TemplatesModal.addEventListener('shown.bs.modal', function () { /* noop: init already run */ });
        }

        function saveTemplates() {
            const saveBtn = document.getElementById('saveTemplatesBtn');
            const templates = {};
            let hasError = false;
            const errors = [];
            templatesTableBody.querySelectorAll('tr').forEach(row => {
                const keyInput = row.querySelector('.template-key');
                const idInput = row.querySelector('.template-id');
                if (keyInput && idInput) {
                    const key = keyInput.value.trim();
                    const id = idInput.value.trim();
                    if (key && id) {
                        if (!/^[a-z0-9_]+$/.test(key)) { hasError = true; errors.push(`Template key "${key}" is invalid. Use only lowercase letters, numbers, and underscores.`); keyInput.classList.add('is-invalid'); }
                        else if (templates[key]) { hasError = true; errors.push(`Duplicate template key: "${key}"`); keyInput.classList.add('is-invalid'); }
                        else { templates[key] = id; keyInput.classList.remove('is-invalid'); idInput.classList.remove('is-invalid'); }
                    } else if (key || id) { hasError = true; errors.push('Both Template Key and Template ID are required.'); if (!key) keyInput.classList.add('is-invalid'); if (!id) idInput.classList.add('is-invalid'); }
                }
            });
            if (hasError) { if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'error', title: 'Validation Error!', html: errors.join('<br>'), confirmButtonText: 'OK' }); } else { alert(errors.join('\n')); } return; }

            const originalBtnText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Saving...';
            const csrfToken = document.querySelector('form[data-csrf]')?.getAttribute('data-csrf') || '';
            const formData = new FormData();
            formData.append('msg91_templates', JSON.stringify(templates));
            if (csrfToken) formData.append('_token', csrfToken);
            const action = getApiAction();
            fetch(action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    let data;
                    if (contentType && contentType.includes('application/json')) data = await response.json();
                    else { const text = await response.text(); throw { status: response.status, data: { message: text || 'An error occurred' } }; }
                    if (!response.ok) throw { status: response.status, data };
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'success', title: 'Success!', text: 'Templates saved successfully', timer: 2000, showConfirmButton: false, timerProgressBar: true }); }
                        const modal = (typeof bootstrap !== 'undefined' && document.getElementById('msg91TemplatesModal')) ? bootstrap.Modal.getInstance(document.getElementById('msg91TemplatesModal')) : null;
                        setTimeout(() => { if (modal) { modal.hide(); } window.location.reload(); }, 1500);
                    } else throw { data };
                })
                .catch(error => {
                    let errorMessage = 'Failed to save templates.';
                    if (error instanceof TypeError && error.message.includes('fetch')) errorMessage = 'Network error. Please check your internet connection.';
                    else if (error.data) {
                        if (error.data.message) errorMessage = error.data.message;
                        else if (error.data.errors) { const errs = Object.values(error.data.errors).flat(); errorMessage = errs.join('<br>'); }
                    }
                    if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'error', title: 'Error!', html: errorMessage, confirmButtonText: 'OK' }); } else alert(errorMessage);
                })
                .finally(() => { saveBtn.disabled = false; saveBtn.innerHTML = originalBtnText; });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Restore active tab
        const savedTab = localStorage.getItem('settingsActiveTab');
        if (savedTab) setTimeout(() => setActiveTab(savedTab), 100);

        // Save tab interactions
        document.querySelectorAll('#vl-pills-tab .nav-link').forEach(tabLink => {
            tabLink.addEventListener('shown.bs.tab', function (e) {
                const activeTab = e.target.getAttribute('href');
                if (activeTab) localStorage.setItem('settingsActiveTab', activeTab);
            });
            tabLink.addEventListener('click', function (e) { const href = this.getAttribute('href'); if (href) localStorage.setItem('settingsActiveTab', href); });
        });

        // initialize forms
        const settingsForm = document.getElementById('settingsForm');
        const updateBtn = document.getElementById('updateSettingsBtn');
        if (settingsForm && updateBtn) handleFormSubmit(settingsForm, updateBtn);

        const settingscontactForm = document.getElementById('settingscontactForm');
        const updatecontactBtn = document.getElementById('updatecontactSettingsBtn');
        if (settingscontactForm && updatecontactBtn) handleFormSubmit(settingscontactForm, updatecontactBtn);

        const basePriceForm = document.getElementById('basePriceForm');
        const updateBasePriceBtn = document.getElementById('updateBasePriceBtn');
        if (basePriceForm && updateBasePriceBtn) handleFormSubmit(basePriceForm, updateBasePriceBtn);

        // Photographer settings form
        const photographerForm = document.getElementById('photographerSettingsForm');
        const savePhotographerBtn = document.getElementById('savePhotographerSettingsBtn');
        if (photographerForm && savePhotographerBtn) handleFormSubmit(photographerForm, savePhotographerBtn);

        // Tour settings form
        const tourForm = document.getElementById('tourForm');
        const saveTourBtn = document.getElementById('saveTourSettingsBtn');
        if (tourForm && saveTourBtn) handleFormSubmit(tourForm, saveTourBtn);

        // Payment gateway logic
        const cashfreeForm = document.getElementById('cashfreeForm');
        const saveCashfreeBtn = document.getElementById('saveCashfreeBtn');
        if (cashfreeForm && saveCashfreeBtn) {
            const cashfreeEnv = document.getElementById('cashfree_env');
            const cashfreeBaseUrl = document.getElementById('cashfree_base_url');
            const cashfreeStatus = document.getElementById('cashfree_status');
            const updateBaseUrl = () => { const env = cashfreeEnv.value; cashfreeBaseUrl.value = env === 'production' ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg'; };
            if (cashfreeEnv) cashfreeEnv.addEventListener('change', updateBaseUrl);
            const toggleCashfreeRequired = () => { const status = cashfreeStatus.checked; document.getElementById('cashfree_app_id').required = status; document.getElementById('cashfree_secret_key').required = status; document.querySelectorAll('.cashfree-required').forEach(el => { el.style.display = status ? 'inline' : 'none'; }); };
            toggleCashfreeRequired();
            cashfreeStatus.addEventListener('change', function () { toggleCashfreeRequired(); saveActivePaymentGateway(); });
            handleFormSubmit(cashfreeForm, saveCashfreeBtn);
        }

        const payuForm = document.getElementById('payuForm');
        const savePayuBtn = document.getElementById('savePayuBtn');
        if (payuForm && savePayuBtn) {
            const payuStatus = document.getElementById('payu_status');
            const togglePayuRequired = () => { const status = payuStatus.checked; document.getElementById('payu_merchant_key').required = status; document.getElementById('payu_merchant_salt').required = status; document.querySelectorAll('.payu-required').forEach(el => { el.style.display = status ? 'inline' : 'none'; }); };
            togglePayuRequired();
            payuStatus.addEventListener('change', function () { togglePayuRequired(); saveActivePaymentGateway(); });
            handleFormSubmit(payuForm, savePayuBtn);
        }

        const razorpayForm = document.getElementById('razorpayForm');
        const saveRazorpayBtn = document.getElementById('saveRazorpayBtn');
        if (razorpayForm && saveRazorpayBtn) {
            const razorpayStatus = document.getElementById('razorpay_status');
            const toggleRazorpayRequired = () => { const status = razorpayStatus.checked; document.getElementById('razorpay_key').required = status; document.getElementById('razorpay_secret').required = status; document.querySelectorAll('.razorpay-required').forEach(el => { el.style.display = status ? 'inline' : 'none'; }); };
            toggleRazorpayRequired();
            razorpayStatus.addEventListener('change', function () { toggleRazorpayRequired(); saveActivePaymentGateway(); });
            handleFormSubmit(razorpayForm, saveRazorpayBtn);
        }

        // SMS gateway forms
        document.querySelectorAll('form[id$="SmsForm"]').forEach(form => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) handleFormSubmit(form, submitBtn);
        });

        document.querySelectorAll('.set-active-sms-gateway-btn').forEach(button => {
            button.addEventListener('click', function () {
                const gatewayKey = this.getAttribute('data-gateway');
                const form = this.closest('form');
                const csrfToken = form.getAttribute('data-csrf');
                const formData = new FormData();
                formData.append('active_sms_gateway', gatewayKey);
                if (csrfToken) formData.append('_token', csrfToken);
                this.disabled = true;
                this.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Setting...';
                const action = form.action || getApiAction();
                fetch(action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(async response => { const data = await response.json(); if (!response.ok) throw { status: response.status, data }; return data; })
                    .then(data => { if (data.success) { if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'success', title: 'Success!', text: gatewayKey.toUpperCase() + ' is now your active SMS gateway', timer: 2000, showConfirmButton: false, timerProgressBar: true }); } setTimeout(() => window.location.reload(), 1500); } else throw { data }; })
                    .catch(error => {
                        let errorMessage = 'Failed to set active gateway.';
                        if (error.data && error.data.message) errorMessage = error.data.message;
                        if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'error', title: 'Error!', text: errorMessage, confirmButtonText: 'OK' }); } else alert(errorMessage);
                        this.disabled = false;
                        this.innerHTML = '<i class="ri-check-line me-1"></i> Set as Active';
                    });
            });
        });

        document.querySelectorAll('.gateway-status-toggle').forEach(checkbox => {
            const gatewayKey = checkbox.getAttribute('data-gateway');
            const form = checkbox.closest('form');
            if (form) {
                const requiredFields = form.querySelectorAll('.' + gatewayKey + '-sms-required');
                requiredFields.forEach(el => { el.style.display = checkbox.checked ? 'inline' : 'none'; });
                const requiredInputs = form.querySelectorAll('input[type="text"], input[type="password"], input[type="number"], select');
                requiredInputs.forEach(input => {
                    const fieldContainer = input.closest('.mb-3');
                    if (fieldContainer && fieldContainer.querySelector('.' + gatewayKey + '-sms-required')) input.required = checkbox.checked;
                });
            }

            checkbox.addEventListener('change', function () {
                const gatewayKey = this.getAttribute('data-gateway');
                const form = this.closest('form');
                const csrfToken = form ? form.getAttribute('data-csrf') : '';
                const originalChecked = !this.checked;
                const formData = new FormData();
                formData.append(this.name, this.checked ? '1' : '0');
                if (csrfToken) formData.append('_token', csrfToken);
                const action = form.action || getApiAction();
                fetch(action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(async response => {
                        const contentType = response.headers.get('content-type');
                        let data;
                        if (contentType && contentType.includes('application/json')) data = await response.json();
                        else { const text = await response.text(); throw { status: response.status, data: { message: text || 'An error occurred' } }; }
                        if (!response.ok) throw { status: response.status, data };
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            if (form) {
                                const requiredFields = form.querySelectorAll('.' + gatewayKey + '-sms-required');
                                requiredFields.forEach(el => { el.style.display = this.checked ? 'inline' : 'none'; });
                                const requiredInputs = form.querySelectorAll('input[type="text"], input[type="password"], input[type="number"], select');
                                requiredInputs.forEach(input => { const fieldContainer = input.closest('.mb-3'); if (fieldContainer && fieldContainer.querySelector('.' + gatewayKey + '-sms-required')) input.required = this.checked; });
                            }
                            if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'success', title: 'Updated!', text: 'SMS gateway status updated successfully', timer: 1500, showConfirmButton: false, timerProgressBar: true, toast: true, position: 'top-end' }); }
                        } else throw { data };
                    })
                    .catch(error => {
                        this.checked = originalChecked;
                        let errorMessage = 'Failed to update gateway status.';
                        if (error instanceof TypeError && error.message.includes('fetch')) errorMessage = 'Network error. Please check your internet connection.';
                        else if (error.data) {
                            if (error.data.message) errorMessage = error.data.message;
                            else if (error.data.errors) { const errs = Object.values(error.data.errors).flat(); errorMessage = errs.join('<br>'); }
                        }
                        if (typeof Swal !== 'undefined') { Swal.fire({ icon: 'error', title: 'Error!', html: errorMessage, timer: 3000, showConfirmButton: false, timerProgressBar: true, toast: true, position: 'top-end' }); } else alert(errorMessage);
                    });
            });
        });

        initTemplateManagement();
        initPanelCardActions();

        // FTP Configuration Management
        initFtpConfigurationManagement();
    });

    // FTP Configuration Management Functions
    function initFtpConfigurationManagement() {
        let ftpConfigModal = null;
        let editingFtpConfigId = null;

        // Initialize modal
        const modalElement = document.getElementById('ftpConfigModal');
        if (!modalElement) return;

        ftpConfigModal = new bootstrap.Modal(modalElement);

        // Helper function to get base URL for admin API routes
        function getAdminApiUrl(path) {
            const basePath = window.location.pathname.split('/admin')[0] || '';
            return basePath + '/admin/api' + (path.startsWith('/') ? path : '/' + path);
        }

        // Load FTP configurations on page load
        loadFtpConfigurations();

        // Add button handler
        const addBtn = document.getElementById('addFtpConfigBtn');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                resetFtpConfigForm();
                editingFtpConfigId = null;
                document.getElementById('ftpConfigModalTitle').textContent = 'Add FTP Configuration';
                ftpConfigModal.show();
            });
        }

        // Form submit handler
        const form = document.getElementById('ftpConfigForm');
        if (form) {
            form.addEventListener('submit', handleFtpConfigSubmit);
        }

        function loadFtpConfigurations() {
            fetch(getAdminApiUrl('/ftp-configurations'), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('FTP Configurations loaded:', data);
                    if (data.success && data.data) {
                        renderFtpConfigurationsTable(data.data);
                    } else {
                        console.error('Unexpected response format:', data);
                    }
                })
                .catch(error => {
                    console.error('Error loading FTP configurations:', error);
                });
        }

        function renderFtpConfigurationsTable(configs) {
            const tbody = document.getElementById('ftpConfigurationsTableBody');
            if (!tbody) return;

            if (!configs || configs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No FTP configurations found. Click "Add FTP Configuration" to create one.</td></tr>';
                return;
            }

            tbody.innerHTML = configs.map(config => `
                <tr>
                    <td><strong>${config.category_name}</strong></td>
                    <td>${config.display_name}</td>
                    <td>${config.main_url}</td>
                    <td><span class="badge bg-info">${(config.driver || 'FTP').toUpperCase()}</span></td>
                    <td>${config.host || ''}</td>
                    <td>${config.port || ''}</td>
                    <td>
                        <span class="badge ${config.is_active ? 'bg-success' : 'bg-secondary'}">
                            ${config.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 justify-content-center">
                            <button class="btn btn-sm btn-soft-info" onclick="window.editFtpConfig(${config.id})"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Edit FTP Configuration">
                                <iconify-icon icon="solar:pen-new-square-broken" class="align-middle fs-18"></iconify-icon>
                            </button>
                            <button class="btn btn-sm btn-soft-danger" onclick="window.deleteFtpConfig(${config.id})"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Delete FTP Configuration">
                                <iconify-icon icon="solar:trash-bin-minimalistic-broken" class="align-middle fs-18"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
            // Re-initialize tooltips for the freshly rendered action buttons
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        }

        window.editFtpConfig = function (id) {
            fetch(getAdminApiUrl(`/ftp-configurations/${id}`), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const config = data.data;
                        populateFtpConfigForm(config);
                        editingFtpConfigId = id;
                        document.getElementById('ftpConfigModalTitle').textContent = 'Edit FTP Configuration';
                        ftpConfigModal.show();
                    }
                })
                .catch(error => {
                    console.error('Error loading FTP configuration:', error);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to load FTP configuration', timer: 2000, showConfirmButton: false });
                    }
                });
        };

        function populateFtpConfigForm(config) {
            document.getElementById('ftp_config_id').value = config.id || '';
            document.getElementById('ftp_category_name').value = config.category_name || '';
            document.getElementById('ftp_display_name').value = config.display_name || '';
            document.getElementById('ftp_main_url').value = config.main_url || '';
            document.getElementById('ftp_driver').value = config.driver || 'ftp';
            document.getElementById('ftp_host').value = config.host || '';
            document.getElementById('ftp_port').value = config.port || 21;
            document.getElementById('ftp_username').value = config.username || '';
            document.getElementById('ftp_password').value = ''; // Don't populate password for security
            document.getElementById('ftp_root').value = config.root || '/';
            document.getElementById('ftp_timeout').value = config.timeout || 30;
            document.getElementById('ftp_passive').checked = config.passive !== false;
            document.getElementById('ftp_ssl').checked = config.ssl === true;
            document.getElementById('ftp_is_active').checked = config.is_active !== false;
            document.getElementById('ftp_remote_path_pattern').value = config.remote_path_pattern || '{customer_id}/{slug}/index.php';
            document.getElementById('ftp_url_pattern').value = config.url_pattern || 'https://{main_url}/{remote_path}';
            document.getElementById('ftp_notes').value = config.notes || '';
        }

        function resetFtpConfigForm() {
            const form = document.getElementById('ftpConfigForm');
            if (form) form.reset();
            document.getElementById('ftp_config_id').value = '';
        }

        function handleFtpConfigSubmit(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            // Convert checkboxes to boolean
            data.passive = formData.has('passive');
            data.ssl = formData.has('ssl');
            data.is_active = formData.has('is_active');

            // Convert port and timeout to integers
            if (data.port) data.port = parseInt(data.port);
            if (data.timeout) data.timeout = parseInt(data.timeout);

            fetch(getAdminApiUrl('/ftp-configurations'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: 'Success!', text: data.message, timer: 2000, showConfirmButton: false });
                        } else {
                            alert(data.message);
                        }
                        ftpConfigModal.hide();
                        loadFtpConfigurations();
                    } else {
                        let errorMessage = data.message || 'Failed to save FTP configuration';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Error!', text: errorMessage, timer: 3000, showConfirmButton: false });
                        } else {
                            alert('Error: ' + errorMessage);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error saving FTP configuration:', error);
                    const errorMessage = 'Error saving FTP configuration. Please try again.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error!', text: errorMessage, timer: 3000, showConfirmButton: false });
                    } else {
                        alert(errorMessage);
                    }
                });
        }

        window.deleteFtpConfig = function (id) {
            if (!confirm('Are you sure you want to delete this FTP configuration?')) {
                return;
            }

            fetch(getAdminApiUrl(`/ftp-configurations/${id}`), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, timer: 2000, showConfirmButton: false });
                        } else {
                            alert(data.message);
                        }
                        loadFtpConfigurations();
                    } else {
                        const errorMessage = data.message || 'Failed to delete FTP configuration';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'Error!', text: errorMessage, timer: 3000, showConfirmButton: false });
                        } else {
                            alert('Error: ' + errorMessage);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error deleting FTP configuration:', error);
                    const errorMessage = 'Error deleting FTP configuration. Please try again.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Error!', text: errorMessage, timer: 3000, showConfirmButton: false });
                    } else {
                        alert(errorMessage);
                    }
                });
        };
    }
})();