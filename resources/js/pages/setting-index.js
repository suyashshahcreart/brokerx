// CSS Import: CSS for Settings page
import '../../css/pages/setting-index.css';

// NOTE: jQuery and Select2 are loaded globally by app.js via Vite
// We will wait for them to be available rather than importing again
// This ensures we use the same instances and avoid conflicts

// Settings page JS — consolidated from Blade inline script
(function () {
    'use strict';

    // Build admin API URL from globals
    function getAdminApiUrl(path) {
        const base = (window.appBaseUrl || '').replace(/\/$/, '');
        const adminBase = (window.adminBasePath || 'ppadmlog').replace(/\/$/, '');
        const normalized = path.startsWith('/') ? path : `/${path}`;
        return `${base}/${adminBase}/api${normalized}`;
    }

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
            // Checkboxes don't send values when unchecked, so we need to explicitly add them
            if (form.id === 'cashfreeForm' || form.id === 'payuForm' || form.id === 'razorpayForm' || form.id === 'portfolioApiForm') {
                const checkboxIdMap = {
                    'cashfreeForm': 'cashfree_status',
                    'payuForm': 'payu_status',
                    'razorpayForm': 'razorpay_status',
                    'portfolioApiForm': 'portfolio_api_enabled'
                };
                const checkboxId = checkboxIdMap[form.id];
                const checkbox = document.getElementById(checkboxId);
                if (checkbox) {
                    // Remove existing value if present (in case it was added by form)
                    formData.delete(checkboxId);
                    // Always set the value - '1' if checked, '0' if not checked
                    formData.append(checkboxId, checkbox.checked ? '1' : '0');
                }
            }

            if (!formData.has('_token')) {
                const token = form.getAttribute('data-csrf');
                if (token) formData.append('_token', token);
            }

            // Get CSRF token from meta tag or form attribute
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                            form.getAttribute('data-csrf') || '';
            
            // Ensure CSRF token is in formData
            if (!formData.has('_token') && csrfToken) {
                formData.append('_token', csrfToken);
            }

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
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
                        // Handle "No changes detected" response
                        const message = data.message || (form.dataset.message || 'Settings updated successfully');
                        const isNoChanges = message.toLowerCase().includes('no changes') || 
                                           message.toLowerCase().includes('no settings to update') ||
                                           (data.data && Array.isArray(data.data) && data.data.length === 0);
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: isNoChanges ? 'info' : 'success',
                                title: isNoChanges ? 'No Changes' : 'Success!',
                                text: message,
                                timer: isNoChanges ? 2500 : 2000,
                                showConfirmButton: false,
                                timerProgressBar: true,
                                toast: true,
                                position: 'top-end'
                            });
                        } else {
                            alert(message);
                        }
                        
                        // Only reload if there were actual changes (not "no changes detected")
                        // Check if data.data exists and has items, or if message indicates changes were made
                        const hasChanges = !isNoChanges && (
                            (data.data && Array.isArray(data.data) && data.data.length > 0) ||
                            message.toLowerCase().includes('updated') ||
                            message.toLowerCase().includes('saved')
                        );
                        
                        if (hasChanges) {
                            if (activeTab) localStorage.setItem('settingsActiveTab', activeTab);
                            setTimeout(() => window.location.reload(), 1200);
                        }
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

    // Cloudflare Cache handlers
    function initCloudflareCacheHandlers() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const tourSelect = document.getElementById('tourSelect');
        const purgeSelectedBtn = document.getElementById('purgeSelectedBtn');
        let tourSelect2Instance = null; // Store Select2 instance
        
        // Show/Hide API Token
        const showCloudflareToken = document.getElementById('showCloudflareToken');
        const cloudflareApiToken = document.getElementById('cloudflare_api_token');
        if (showCloudflareToken && cloudflareApiToken) {
            showCloudflareToken.addEventListener('change', function() {
                cloudflareApiToken.type = this.checked ? 'text' : 'password';
            });
        }

        // Inject critical Select2 CSS if not already loaded
        function ensureSelect2CSS() {
            // Check if Select2 CSS is loaded by looking for Select2 styles
            const testEl = document.createElement('div');
            testEl.className = 'select2-container';
            testEl.style.position = 'absolute';
            testEl.style.visibility = 'hidden';
            document.body.appendChild(testEl);
            const computedStyle = window.getComputedStyle(testEl);
            const hasSelect2CSS = computedStyle.position !== 'static' || document.querySelector('link[href*="select2"]');
            document.body.removeChild(testEl);
            
            if (!hasSelect2CSS) {
                console.warn('⚠️ Select2 CSS not detected, injecting critical styles...');
                const style = document.createElement('style');
                style.id = 'select2-critical-css';
                style.textContent = `
                    .select2-container { width: 100% !important; display: block; }
                    .select2-selection--multiple { min-height: 38px; border: 1px solid #ced4da; border-radius: 0.375rem; padding: 2px 8px; }
                    .select2-selection--multiple .select2-selection__choice { background-color: #0d6efd; border: 1px solid #0d6efd; color: #fff; border-radius: 0.25rem; padding: 2px 8px; margin: 2px 4px 2px 0; display: inline-flex; align-items: center; }
                    .select2-selection--multiple .select2-selection__choice__remove { color: #fff; cursor: pointer; margin-right: 5px; opacity: 0.8; }
                    .select2-selection--multiple .select2-selection__choice__remove:hover { opacity: 1; }
                    .select2-dropdown { border: 1px solid #ced4da; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
                    .select2-results__option { padding: 0.5rem 0.75rem; }
                    .select2-results__option--highlighted { background-color: #0d6efd; color: #fff; }
                `;
                document.head.appendChild(style);
            }
        }
        
        // Initialize Select2 for tour select
        function initSelect2(retryCount = 0) {
            if (!tourSelect) {
                console.warn('initSelect2: tourSelect element not found');
                return;
            }
            
            const maxRetries = 10; // Wait up to 12 seconds total (60 * 200ms)
            const retryDelay = 100; // Delay between retries
            const cdnFallbackDelay = 10; // Start CDN fallback after 2 seconds (10 retries)
            
            // Get jQuery instance (from app.js)
            const $jq = window.$ || window.jQuery;
            
            // Check if jQuery and Select2 are available
            const hasJQuery = typeof $jq !== 'undefined';
            const hasSelect2 = hasJQuery && typeof $jq.fn !== 'undefined' && typeof $jq.fn.select2 !== 'undefined';
            
            // Start CDN fallback early if Select2 isn't available
            if (retryCount === cdnFallbackDelay && !hasSelect2 && !window.__select2CDNLoading && !window.__select2CDNLoaded) {
                console.warn('⚠️ Select2 not available from app.js after 2 seconds. Loading from CDN as fallback...');
                loadSelect2FromCDN();
            }
            
            // Debug logging (reduced frequency)
            if (retryCount === 0 || retryCount % 15 === 0 || retryCount >= maxRetries - 3) {
                console.log(`initSelect2: Attempt ${retryCount + 1}/${maxRetries + 1}`);
                console.log('  jQuery available:', hasJQuery);
                console.log('  Select2 plugin available:', hasSelect2);
            }
            
            if (hasJQuery && hasSelect2) {
                try {
                    // Ensure CSS is loaded before initializing
                    ensureSelect2CSS();
                    
                    // Destroy existing Select2 instance if it exists
                    if (tourSelect2Instance) {
                        try {
                            $jq(tourSelect).select2('destroy');
                        } catch (e) {
                            // Ignore destroy errors
                        }
                        tourSelect2Instance = null;
                    }
                    
                    // Ensure element is visible and ready
                    if (tourSelect.offsetParent === null && tourSelect.style.display === 'none') {
                        // Element might be hidden in tab, wait a bit more
                        if (retryCount < maxRetries) {
                            setTimeout(() => {
                                initSelect2(retryCount + 1);
                            }, retryDelay);
                        }
                        return;
                    }
                    
                    // Initialize Select2 with proper configuration
                    const select2Config = {
                        width: '100%',
                        placeholder: 'Select tours to purge...',
                        allowClear: false,
                        closeOnSelect: false,
                        dropdownParent: $jq(tourSelect).closest('.tab-pane, .card-body, body')
                    };
                    
                    // Try Bootstrap 5 theme, fallback to default
                    try {
                        tourSelect2Instance = $jq(tourSelect).select2({
                            ...select2Config,
                            theme: 'bootstrap-5'
                        });
                    } catch (e) {
                        console.warn('Bootstrap 5 theme not available, using default theme');
                        tourSelect2Instance = $jq(tourSelect).select2(select2Config);
                    }
                    
                    // Force apply styles after initialization
                    setTimeout(() => {
                        const container = $jq(tourSelect).next('.select2-container');
                        if (container.length) {
                            container.css({
                                'width': '100%',
                                'display': 'block'
                            });
                            const selection = container.find('.select2-selection--multiple');
                            if (selection.length) {
                                selection.css({
                                    'min-height': '38px',
                                    'border': '1px solid #ced4da',
                                    'border-radius': '0.375rem',
                                    'padding': '2px 8px'
                                });
                            }
                        }
                    }, 100);
                    
                    console.log('✅ Select2 initialized successfully for tour select');
                    
                    // Update button state when selection changes
                    $jq(tourSelect).off('change.select2-cloudflare').on('change.select2-cloudflare', function() {
                        const selectedValues = $jq(this).val();
                        if (purgeSelectedBtn) {
                            purgeSelectedBtn.disabled = !selectedValues || selectedValues.length === 0;
                        }
                    });
                    
                    // Mark as successfully initialized
                    window.__select2Initialized = true;
                } catch (error) {
                    console.error('❌ Error initializing Select2:', error);
                    console.error('Error details:', error.message);
                    // Continue retrying on error
                    if (retryCount < maxRetries) {
                        setTimeout(() => {
                            initSelect2(retryCount + 1);
                        }, retryDelay);
                    }
                }
            } else {
                // Retry if Select2 is not yet available
                if (retryCount < maxRetries) {
                    setTimeout(() => {
                        initSelect2(retryCount + 1);
                    }, retryDelay);
                } else {
                    // Final failure - this should not happen if CDN loads successfully
                    console.error('❌ CRITICAL: Select2 not available after all retries.');
                    console.error('Final check - jQuery:', hasJQuery, 'Select2:', hasSelect2);
                    if (!hasJQuery) {
                        console.error('❌ jQuery is not available. This is a critical error.');
                    }
                }
            }
        }
        
        // Fallback: Load Select2 from CDN if app.js didn't load it
        function loadSelect2FromCDN() {
            // Check if already loading or loaded
            if (window.__select2CDNLoading || window.__select2CDNLoaded) {
                return;
            }
            window.__select2CDNLoading = true;
            
            console.log('📦 Loading Select2 from CDN...');
            
            // Check if CSS is already loaded
            const existingCSS = document.querySelector('link[href*="select2"]');
            if (!existingCSS) {
                // Load Select2 base CSS first
                const cssLink = document.createElement('link');
                cssLink.rel = 'stylesheet';
                cssLink.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
                cssLink.onload = function() {
                    console.log('✅ Select2 base CSS loaded');
                    // Load Bootstrap 5 theme CSS
                    const themeCSS = document.createElement('link');
                    themeCSS.rel = 'stylesheet';
                    themeCSS.href = 'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css';
                    themeCSS.onload = function() {
                        console.log('✅ Select2 Bootstrap 5 theme CSS loaded');
                    };
                    themeCSS.onerror = function() {
                        console.warn('⚠️ Select2 Bootstrap 5 theme CSS failed to load, using base styles');
                    };
                    document.head.appendChild(themeCSS);
                };
                cssLink.onerror = function() {
                    console.error('❌ Failed to load Select2 CSS from CDN');
                    // Try alternative CDN
                    const altCSS = document.createElement('link');
                    altCSS.rel = 'stylesheet';
                    altCSS.href = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css';
                    document.head.appendChild(altCSS);
                };
                document.head.appendChild(cssLink);
            } else {
                console.log('✅ Select2 CSS already loaded');
            }
            
            // Load Select2 JS
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
            script.onload = function() {
                console.log('✅ Select2 loaded from CDN successfully');
                window.__select2CDNLoading = false;
                window.__select2CDNLoaded = true;
                
                // Verify Select2 attached to jQuery
                const $jq = window.$ || window.jQuery;
                if ($jq && typeof $jq.fn !== 'undefined' && typeof $jq.fn.select2 !== 'undefined') {
                    console.log('✅ Select2 attached to jQuery from CDN');
                    // Retry initialization immediately
                    setTimeout(() => {
                        initSelect2(0); // Retry from beginning
                    }, 100);
                } else {
                    console.error('❌ Select2 loaded from CDN but did not attach to jQuery');
                }
            };
            script.onerror = function() {
                console.error('❌ Failed to load Select2 JS from CDN');
                window.__select2CDNLoading = false;
                // Try alternative CDN
                console.warn('⚠️ Trying alternative CDN...');
                const altScript = document.createElement('script');
                altScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js';
                altScript.onload = function() {
                    console.log('✅ Select2 loaded from alternative CDN');
                    window.__select2CDNLoaded = true;
                    setTimeout(() => {
                        initSelect2(0);
                    }, 100);
                };
                document.head.appendChild(altScript);
            };
            document.head.appendChild(script);
        }

        // Load bookings with tours for Custom Purge dropdown
        function loadBookingsWithTours() {
            if (!tourSelect) return;

            // Show loading state
            const $jq = window.$ || window.jQuery;
            if (tourSelect2Instance && $jq && typeof $jq.fn !== 'undefined' && typeof $jq.fn.select2 !== 'undefined') {
                try {
                    $jq(tourSelect).select2('destroy');
                } catch (e) {
                    console.warn('Error destroying Select2:', e);
                }
                tourSelect2Instance = null;
            }
            tourSelect.innerHTML = '<option value="" disabled>Loading tours...</option>';

            fetch(getAdminApiUrl('/cloudflare/bookings-with-tours'), {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    tourSelect.innerHTML = '';
                    if (data.data.length === 0) {
                        tourSelect.innerHTML = '<option value="" disabled>No tours available</option>';
                    } else {
                        data.data.forEach(booking => {
                            const option = document.createElement('option');
                            option.value = booking.tour_code;
                            option.textContent = `${booking.tour_code} - ${booking.tour_title} (${booking.tour_name})`;
                            tourSelect.appendChild(option);
                        });
                    }
                    
                    // Initialize Select2 after options are loaded (small delay to ensure DOM is ready)
                    setTimeout(() => {
                        initSelect2();
                    }, 50);
                } else {
                    tourSelect.innerHTML = '<option value="" disabled>Error loading tours</option>';
                    setTimeout(() => {
                        initSelect2();
                    }, 50);
                }
            })
            .catch(error => {
                console.error('Error loading tours:', error);
                tourSelect.innerHTML = '<option value="" disabled>Error loading tours</option>';
                setTimeout(() => {
                    initSelect2();
                }, 50);
            });
        }

        // Load tours when Purge Cache tab is shown
        const cloudflarePurgeTab = document.getElementById('cloudflare-purge-inner-tab');
        if (cloudflarePurgeTab) {
            // Check if tab is already active on page load
            const purgeTabPane = document.getElementById('cloudflare-purge-tabpane');
            if (purgeTabPane && purgeTabPane.classList.contains('active')) {
                // Wait a bit for tab to be fully rendered
                setTimeout(() => {
                    loadBookingsWithTours();
                }, 100);
            }
            
            // Load when tab is shown
            cloudflarePurgeTab.addEventListener('shown.bs.tab', function() {
                // Wait a bit for tab animation to complete
                setTimeout(() => {
                    loadBookingsWithTours();
                }, 100);
            });
        }

        // Enable/disable Purge Selected button based on selection (fallback for non-Select2)
        if (tourSelect && purgeSelectedBtn && (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined')) {
            tourSelect.addEventListener('change', function() {
                purgeSelectedBtn.disabled = this.selectedOptions.length === 0;
            });
        }

        // Purge Everything button
        const purgeEverythingBtn = document.getElementById('purgeEverythingBtn');
        if (purgeEverythingBtn) {
            purgeEverythingBtn.addEventListener('click', function() {
                if (typeof Swal === 'undefined') {
                    if (!confirm('Are you sure you want to purge all cache? This action cannot be undone.')) {
                        return;
                    }
                } else {
                    Swal.fire({
                        title: 'Purge Everything?',
                        text: 'This will purge all cache for tours and settings. This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, purge everything!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performPurgeEverything();
                        }
                    });
                    return;
                }
                performPurgeEverything();
            });
        }

        // Purge Selected button
        if (purgeSelectedBtn) {
            purgeSelectedBtn.addEventListener('click', function() {
                if (!tourSelect) return;
                
                // Get selected values from Select2 if available, otherwise use native select
                let selectedTours = [];
                const $jq = window.$ || window.jQuery;
                if ($jq && typeof $jq.fn !== 'undefined' && typeof $jq.fn.select2 !== 'undefined' && tourSelect2Instance) {
                    try {
                        selectedTours = $jq(tourSelect).val() || [];
                        selectedTours = selectedTours.filter(v => v); // Remove empty values
                    } catch (e) {
                        console.warn('Error getting Select2 values, falling back to native select:', e);
                        selectedTours = Array.from(tourSelect.selectedOptions).map(opt => opt.value).filter(v => v);
                    }
                } else {
                    selectedTours = Array.from(tourSelect.selectedOptions).map(opt => opt.value).filter(v => v);
                }
                
                if (selectedTours.length === 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Selection',
                            text: 'Please select at least one tour to purge.'
                        });
                    } else {
                        alert('Please select at least one tour to purge.');
                    }
                    return;
                }

                console.log('Selected tours for purge:', selectedTours); // Debug log

                if (typeof Swal === 'undefined') {
                    if (!confirm(`Are you sure you want to purge cache for ${selectedTours.length} selected tour(s)?`)) {
                        return;
                    }
                } else {
                    Swal.fire({
                        title: 'Purge Selected Tours?',
                        text: `This will purge cache for ${selectedTours.length} selected tour(s).`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, purge selected!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            performCustomPurge(selectedTours);
                        }
                    });
                    return;
                }
                performCustomPurge(selectedTours);
            });
        }

        function performPurgeEverything() {
            const btn = purgeEverythingBtn;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Purging...';

            fetch(getAdminApiUrl('/cloudflare/purge'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    purge_everything: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Cache purged successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(data.message || 'Cache purged successfully.');
                    }
                } else {
                    throw new Error(data.message || 'Failed to purge cache.');
                }
            })
            .catch(error => {
                const errorMessage = error.message || 'Failed to purge cache. Please try again.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });
                } else {
                    alert(errorMessage);
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }

        function performCustomPurge(tourCodes) {
            const btn = purgeSelectedBtn;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ri-loader-4-line me-1 spin"></i> Purging...';

            console.log('Purging tours:', tourCodes); // Debug log

            fetch(getAdminApiUrl('/cloudflare/purge'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    tour_codes: tourCodes
                })
            })
            .then(response => {
                console.log('Purge response status:', response.status); // Debug log
                return response.json();
            })
            .then(data => {
                console.log('Purge response data:', data); // Debug log
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Cache purged successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(data.message || 'Cache purged successfully.');
                    }
                    // Clear selection - use Select2 API if available
                    const $jq = window.$ || window.jQuery;
                    if ($jq && typeof $jq.fn !== 'undefined' && typeof $jq.fn.select2 !== 'undefined' && tourSelect2Instance) {
                        try {
                            $jq(tourSelect).val(null).trigger('change');
                        } catch (e) {
                            console.warn('Error clearing Select2, falling back to native select:', e);
                            if (tourSelect) {
                                Array.from(tourSelect.options).forEach(option => {
                                    option.selected = false;
                                });
                            }
                        }
                    } else if (tourSelect) {
                        // Clear all selections in native multi-select
                        Array.from(tourSelect.options).forEach(option => {
                            option.selected = false;
                        });
                    }
                    purgeSelectedBtn.disabled = true;
                } else {
                    throw new Error(data.message || 'Failed to purge cache.');
                }
            })
            .catch(error => {
                console.error('Purge error:', error); // Debug log
                const errorMessage = error.message || 'Failed to purge cache. Please try again.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });
                } else {
                    alert(errorMessage);
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
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

        // Portfolio API settings form
        const portfolioApiForm = document.getElementById('portfolioApiForm');
        const updatePortfolioApiBtn = document.getElementById('updatePortfolioApiSettingsBtn');
        if (portfolioApiForm && updatePortfolioApiBtn) handleFormSubmit(portfolioApiForm, updatePortfolioApiBtn);

        // Cloudflare Cache Configuration form
        const cloudflareConfigForm = document.getElementById('cloudflareConfigForm');
        const saveCloudflareConfigBtn = document.getElementById('saveCloudflareConfigBtn');
        if (cloudflareConfigForm && saveCloudflareConfigBtn) handleFormSubmit(cloudflareConfigForm, saveCloudflareConfigBtn);

        // Cloudflare Cache handlers
        initCloudflareCacheHandlers();

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

        // Property Type/Sub Type Management
        initPropertyTypeManagement();

        // FTP Configuration Management
        initFtpConfigurationManagement();

        // State and City Management
        initStateCityManagement();
    });

    function initPropertyTypeManagement() {
        const $ = window.jQuery;
        if (!$ || !$.fn.DataTable) return;

        // Check if property type routes are defined
        if (!window.propertyTypeRoutes) {
            console.error('Property type routes not defined');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const routes = {
            propertyTypes: {
                list: window.propertyTypeRoutes.propertyTypesList,
                options: window.propertyTypeRoutes.propertyTypesOptions,
                store: window.propertyTypeRoutes.propertyTypesStore,
                update: id => window.propertyTypeRoutes.propertyTypesUpdate.replace('__ID__', id),
                destroy: id => window.propertyTypeRoutes.propertyTypesDestroy.replace('__ID__', id)
            },
            subTypes: {
                list: window.propertyTypeRoutes.subTypesList,
                store: window.propertyTypeRoutes.subTypesStore,
                update: id => window.propertyTypeRoutes.subTypesUpdate.replace('__ID__', id),
                destroy: id => window.propertyTypeRoutes.subTypesDestroy.replace('__ID__', id)
            }
        };

        const typeModal = new bootstrap.Modal(document.getElementById('propertyTypeModal'));
        const subTypeModal = new bootstrap.Modal(document.getElementById('propertySubTypeModal'));

        const typeTableEl = $('#property-types-table');
        const subTypeTableEl = $('#property-sub-types-table');
        if (!typeTableEl.length || !subTypeTableEl.length) return;

        const typeTable = typeTableEl.DataTable({
            processing: true,
            serverSide: true,
            ajax: routes.propertyTypes.list,
            order: [[0, 'asc']],
            columns: [
                { data: 'name', name: 'name', className: 'fw-semibold' },
                {
                    data: 'icon',
                    name: 'icon',
                    orderable: false,
                    render: value => renderIcon(value)
                },
                { data: 'sub_types_count', name: 'sub_types_count', defaultContent: 0, className: 'text-center' },
                { data: 'updated_at', name: 'updated_at' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: row => actionButtons('type', row)
                }
            ],
            language: { search: '_INPUT_', searchPlaceholder: 'Search property types...' },
            lengthMenu: [10, 25, 50, 100],
            responsive: true
        });

        const subTypeTable = subTypeTableEl.DataTable({
            processing: true,
            serverSide: true,
            ajax: routes.subTypes.list,
            order: [[1, 'asc']],
            columns: [
                { data: 'property_type_name', name: 'propertyType.name', className: 'fw-semibold', orderable: false },
                { data: 'name', name: 'name' },
                {
                    data: 'icon',
                    name: 'icon',
                    orderable: false,
                    render: value => renderIcon(value)
                },
                { data: 'updated_at', name: 'updated_at' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: row => actionButtons('subType', row)
                }
            ],
            language: { search: '_INPUT_', searchPlaceholder: 'Search sub property types...' },
            lengthMenu: [10, 25, 50, 100],
            responsive: true
        });

        function renderIcon(value) {
            if (!value) return '<span class="text-muted">-</span>';
            const isUrl = /^(http|https):\/\//i.test(value);
            if (isUrl) {
                return `<span class="d-inline-flex align-items-center gap-2"><img src="${value}" alt="icon" height="20" class="rounded"> <span class="text-muted small">Image</span></span>`;
            }
            return `<span class="d-inline-flex align-items-center gap-2"><i class="${value}"></i> <span class="text-muted small">${value}</span></span>`;
        }

        function actionButtons(kind, data) {
            const editClass = kind === 'type' ? 'btn-edit-type' : 'btn-edit-subtype';
            const deleteClass = kind === 'type' ? 'btn-delete-type' : 'btn-delete-subtype';
            const label = kind === 'type' ? 'Property Type' : 'Sub Property Type';
            return `
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-soft-primary ${editClass}" data-id="${data.id}" title="Edit ${label}">
                        <i class="ri-edit-2-line"></i>
                    </button>
                    <button type="button" class="btn btn-soft-danger ${deleteClass}" data-id="${data.id}" data-name="${data.name}" title="Delete ${label}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            `;
        }

        function showErrors(selector, errors) {
            const container = $(selector);
            if (!errors || !Object.keys(errors).length) {
                container.addClass('d-none').empty();
                return;
            }
            const list = Object.values(errors).flat().map(msg => `<div>${msg}</div>`).join('');
            container.html(list).removeClass('d-none');
        }

        function notify(type, message) {
            if (window.Swal) {
                Swal.fire({ toast: true, icon: type, title: message, position: 'top-end', showConfirmButton: false, timer: 2000 });
            } else if (window.toastr && typeof window.toastr[type] === 'function') {
                window.toastr[type](message);
            } else {
                window.alert(message);
            }
        }

        function getRowData(dt, element) {
            const $tr = $(element).closest('tr');
            const row = dt.row($tr);
            return row.data() || dt.row($tr.prev('.parent')).data();
        }

        function resetTypeForm(data = null) {
            $('#propertyTypeForm')[0].reset();
            $('#propertyTypeErrors').addClass('d-none').empty();
            $('#propertyTypeId').val(data ? data.id : '');
            $('#propertyTypeName').val(data ? data.name : '');
            $('#propertyTypeIcon').val(data ? data.icon : '');
            $('#propertyTypeModalLabel').text(data ? 'Edit Property Type' : 'Add Property Type');
            $('#savePropertyTypeBtn').text(data ? 'Update' : 'Save');
        }

        function resetSubTypeForm(data = null) {
            $('#propertySubTypeForm')[0].reset();
            $('#propertySubTypeErrors').addClass('d-none').empty();
            $('#propertySubTypeId').val(data ? data.id : '');
            $('#propertySubTypeName').val(data ? data.name : '');
            $('#propertySubTypeIcon').val(data ? data.icon : '');
            $('#propertySubTypeModalLabel').text(data ? 'Edit Sub Property Type' : 'Add Sub Property Type');
            $('#savePropertySubTypeBtn').text(data ? 'Update' : 'Save');
            loadPropertyTypeOptions(data ? data.property_type_id : null);
        }

        function loadPropertyTypeOptions(selectedId = null) {
            $.get(routes.propertyTypes.options)
                .done(res => {
                    const select = $('#propertySubTypePropertyType');
                    select.empty();
                    const types = res.data || [];
                    if (!types.length) {
                        select.append('<option value="">No property types found</option>');
                        return;
                    }
                    select.append('<option value="">Select property type</option>');
                    types.forEach(item => {
                        const selected = selectedId && Number(selectedId) === Number(item.id) ? 'selected' : '';
                        select.append(`<option value="${item.id}" ${selected}>${item.name}</option>`);
                    });
                })
                .fail(() => notify('error', 'Unable to load property types.'));
        }

        // Open modals
        $('#openPropertyTypeModal').on('click', () => { resetTypeForm(); typeModal.show(); });
        $('#openPropertySubTypeModal, #openPropertySubTypeModalSecondary').on('click', () => { resetSubTypeForm(); subTypeModal.show(); });

        // Edit actions
        $('#property-types-table').on('click', '.btn-edit-type', function () {
            const data = getRowData(typeTable, this);
            if (!data) return;
            resetTypeForm(data);
            typeModal.show();
        });

        $('#property-sub-types-table').on('click', '.btn-edit-subtype', function () {
            const data = getRowData(subTypeTable, this);
            if (!data) return;
            resetSubTypeForm(data);
            $('#propertySubTypePropertyType').val(data.property_type_id);
            subTypeModal.show();
        });

        // Delete actions
        $('#property-types-table').on('click', '.btn-delete-type', function () {
            const data = getRowData(typeTable, this);
            if (!data) return;
            confirmAndDelete('type', data.id, data.name);
        });

        $('#property-sub-types-table').on('click', '.btn-delete-subtype', function () {
            const data = getRowData(subTypeTable, this);
            if (!data) return;
            confirmAndDelete('subType', data.id, data.name);
        });

        function confirmAndDelete(kind, id, name) {
            const label = kind === 'type' ? 'property type' : 'sub property type';
            const proceed = () => {
                const url = kind === 'type' ? routes.propertyTypes.destroy(id) : routes.subTypes.destroy(id);
                $.ajax({ url, method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } })
                    .done(res => {
                        notify('success', res.message || 'Deleted successfully');
                        typeTable.ajax.reload(null, false);
                        subTypeTable.ajax.reload(null, false);
                    })
                    .fail(xhr => {
                        const message = xhr.responseJSON?.message || `Failed to delete ${label}.`;
                        notify('error', message);
                    });
            };

            if (window.Swal) {
                Swal.fire({
                    title: 'Delete Confirmation',
                    text: `Delete ${label} "${name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel',
                    customClass: { confirmButton: 'btn btn-danger me-2', cancelButton: 'btn btn-outline-secondary' },
                    buttonsStyling: false
                }).then(result => { if (result.isConfirmed) proceed(); });
            } else if (window.confirm(`Delete ${label} "${name}"?`)) {
                proceed();
            }
        }

        // Submit type form
        $('#propertyTypeForm').on('submit', function (event) {
            event.preventDefault();
            showErrors('#propertyTypeErrors', null);
            const id = $('#propertyTypeId').val();
            const url = id ? routes.propertyTypes.update(id) : routes.propertyTypes.store;
            const method = id ? 'PUT' : 'POST';
            $.ajax({
                url,
                method,
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .done(res => {
                    notify('success', res.message || 'Property type saved');
                    typeModal.hide();
                    typeTable.ajax.reload(null, false);
                    subTypeTable.ajax.reload(null, false);
                })
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors || null;
                    const message = xhr.responseJSON?.message || 'Unable to save property type.';
                    showErrors('#propertyTypeErrors', errors);
                    if (!errors) notify('error', message);
                });
        });

        // Submit sub type form
        $('#propertySubTypeForm').on('submit', function (event) {
            event.preventDefault();
            showErrors('#propertySubTypeErrors', null);
            const id = $('#propertySubTypeId').val();
            const url = id ? routes.subTypes.update(id) : routes.subTypes.store;
            const method = id ? 'PUT' : 'POST';
            $.ajax({
                url,
                method,
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .done(res => {
                    notify('success', res.message || 'Sub property type saved');
                    subTypeModal.hide();
                    subTypeTable.ajax.reload(null, false);
                    typeTable.ajax.reload(null, false);
                })
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors || null;
                    const message = xhr.responseJSON?.message || 'Unable to save sub property type.';
                    showErrors('#propertySubTypeErrors', errors);
                    if (!errors) notify('error', message);
                });
        });
    }

    // FTP Configuration Management Functions
    function initFtpConfigurationManagement() {
        let ftpConfigModal = null;
        let editingFtpConfigId = null;

        // Initialize modal
        const modalElement = document.getElementById('ftpConfigModal');
        if (!modalElement) return;

        ftpConfigModal = new bootstrap.Modal(modalElement);

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
            // Don't populate password - user must enter it if they want to change it
            const passwordField = document.getElementById('ftp_password');
            const passwordRequired = document.getElementById('ftp_password_required');
            const passwordHelp = document.getElementById('ftp_password_help');
            
            passwordField.value = '';
            if (config.id) {
                // Editing existing record
                passwordField.placeholder = 'Leave blank to keep current password';
                passwordField.required = false;
                if (passwordRequired) passwordRequired.style.display = 'none';
                if (passwordHelp) passwordHelp.textContent = 'Leave blank to keep current password';
            } else {
                // New record
                passwordField.placeholder = 'Enter password';
                passwordField.required = true;
                if (passwordRequired) passwordRequired.style.display = 'inline';
                if (passwordHelp) passwordHelp.textContent = 'Required for new configurations';
            }
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
            // Reset password field placeholder and requirement
            const passwordField = document.getElementById('ftp_password');
            const passwordRequired = document.getElementById('ftp_password_required');
            const passwordHelp = document.getElementById('ftp_password_help');
            
            passwordField.placeholder = 'Enter password';
            passwordField.required = true;
            if (passwordRequired) passwordRequired.style.display = 'inline';
            if (passwordHelp) passwordHelp.textContent = 'Required for new configurations';
        }

        function handleFtpConfigSubmit(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            // Remove password if it's empty (for updates)
            if (!data.password || data.password.trim() === '') {
                delete data.password;
            }

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

    // State and City Management Functions
    function initStateCityManagement() {
        const $ = window.jQuery;
        if (!$ || !$.fn.DataTable) return;

        // Check if state city routes are defined
        if (!window.stateCityRoutes) {
            console.error('State city routes not defined');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        const routes = {
            countries: {
                list: window.stateCityRoutes.countriesList,
                options: window.stateCityRoutes.countriesOptions,
                store: window.stateCityRoutes.countriesStore,
                update: id => window.stateCityRoutes.countriesUpdate.replace('__ID__', id),
                destroy: id => window.stateCityRoutes.countriesDestroy.replace('__ID__', id)
            },
            states: {
                list: window.stateCityRoutes.statesList,
                options: window.stateCityRoutes.statesOptions,
                store: window.stateCityRoutes.statesStore,
                update: id => window.stateCityRoutes.statesUpdate.replace('__ID__', id),
                destroy: id => window.stateCityRoutes.statesDestroy.replace('__ID__', id)
            },
            cities: {
                list: window.stateCityRoutes.citiesList,
                options: window.stateCityRoutes.citiesOptions,
                store: window.stateCityRoutes.citiesStore,
                update: id => window.stateCityRoutes.citiesUpdate.replace('__ID__', id),
                destroy: id => window.stateCityRoutes.citiesDestroy.replace('__ID__', id)
            }
        };

        const countryModalEl = document.getElementById('countryModal');
        const stateModalEl = document.getElementById('stateModal');
        const cityModalEl = document.getElementById('cityModal');

        const countryModal = countryModalEl ? new bootstrap.Modal(countryModalEl) : null;
        const stateModal = stateModalEl ? new bootstrap.Modal(stateModalEl) : null;
        const cityModal = cityModalEl ? new bootstrap.Modal(cityModalEl) : null;

        const countryTableEl = $('#countries-table');
        const countryStatusFilter = document.getElementById('countryStatusFilter');
        const countryStatusFilterLabel = document.getElementById('countryStatusFilterLabel');
        const stateTableEl = $('#states-table');
        const cityTableEl = $('#cities-table');
        if (!countryTableEl.length && !stateTableEl.length && !cityTableEl.length) return;

        const getCountryStatusFilter = () => {
            if (!countryStatusFilter) return null;
            return countryStatusFilter.checked ? 'active' : 'inactive';
        };

        const syncCountryStatusLabel = () => {
            if (!countryStatusFilterLabel || !countryStatusFilter) return;
            countryStatusFilterLabel.textContent = countryStatusFilter.checked ? 'Active' : 'Inactive';
        };

        syncCountryStatusLabel();

        // Countries DataTable
        const countryTable = countryTableEl.length ? countryTableEl.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: routes.countries.list,
                data: function (d) {
                    const status = getCountryStatusFilter();
                    if (status) {
                        d.status = status;
                    }
                }
            },
            order: [[0, 'asc']],
            columns: [
                { data: 'name', name: 'name', className: 'fw-semibold' },
                { data: 'country_code', name: 'country_code' },
                { data: 'dial_code', name: 'dial_code' },
                {
                    data: 'is_active',
                    name: 'is_active',
                    className: 'text-center',
                    render: value => value ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'
                },
                { data: 'updated_at', name: 'updated_at' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: row => actionButtons('country', row)
                }
            ],
            language: { search: '_INPUT_', searchPlaceholder: 'Search countries...' },
            lengthMenu: [10, 25, 50, 100],
            responsive: true
        }) : null;

        // States DataTable
        const stateTable = stateTableEl.length ? stateTableEl.DataTable({
            processing: true,
            serverSide: true,
            ajax: routes.states.list,
            order: [[0, 'asc']],
            columns: [
                { data: 'name', name: 'name', className: 'fw-semibold' },
                { data: 'cities_count', name: 'cities_count', defaultContent: 0, className: 'text-center' },
                { data: 'updated_at', name: 'updated_at' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: row => actionButtons('state', row)
                }
            ],
            language: { search: '_INPUT_', searchPlaceholder: 'Search states...' },
            lengthMenu: [10, 25, 50, 100],
            responsive: true
        }) : null;

        // Cities DataTable
        const cityTable = cityTableEl.length ? cityTableEl.DataTable({
            processing: true,
            serverSide: true,
            ajax: routes.cities.list,
            order: [[1, 'asc']],
            columns: [
                { data: 'state_name', name: 'state.name', className: 'fw-semibold', orderable: false },
                { data: 'name', name: 'name' },
                { data: 'updated_at', name: 'updated_at' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: row => actionButtons('city', row)
                }
            ],
            language: { search: '_INPUT_', searchPlaceholder: 'Search cities...' },
            lengthMenu: [10, 25, 50, 100],
            responsive: true
        }) : null;

        function actionButtons(kind, data) {
            const editClass = kind === 'country'
                ? 'btn-edit-country'
                : (kind === 'state' ? 'btn-edit-state' : 'btn-edit-city');
            const deleteClass = kind === 'country'
                ? 'btn-delete-country'
                : (kind === 'state' ? 'btn-delete-state' : 'btn-delete-city');
            const label = kind === 'country' ? 'Country' : (kind === 'state' ? 'State' : 'City');
            return `
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-soft-primary ${editClass}" data-id="${data.id}" title="Edit ${label}">
                        <i class="ri-edit-2-line"></i>
                    </button>
                    <button type="button" class="btn btn-soft-danger ${deleteClass}" data-id="${data.id}" data-name="${data.name}" title="Delete ${label}">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            `;
        }

        function showErrors(selector, errors) {
            const container = $(selector);
            if (!errors || !Object.keys(errors).length) {
                container.addClass('d-none').empty();
                return;
            }
            const list = Object.values(errors).flat().map(msg => `<div>${msg}</div>`).join('');
            container.html(list).removeClass('d-none');
        }

        function notify(type, message) {
            if (window.Swal) {
                Swal.fire({ icon: type, title: type === 'success' ? 'Success!' : 'Error!', text: message, timer: 2000, showConfirmButton: false });
            } else {
                alert(message);
            }
        }

        function getRowData(dt, element) {
            const $tr = $(element).closest('tr');
            const row = dt.row($tr);
            return row.data() || dt.row($tr.prev('.parent')).data();
        }

        function resetStateForm(data = null) {
            $('#stateForm')[0].reset();
            $('#stateErrors').addClass('d-none').empty();
            $('#stateId').val(data ? data.id : '');
            $('#stateName').val(data ? data.name : '');
            $('#stateModalLabel').text(data ? 'Edit State' : 'Add State');
            $('#saveStateBtn').text(data ? 'Update' : 'Save');
        }

        function resetCountryForm(data = null) {
            $('#countryForm')[0].reset();
            $('#countryErrors').addClass('d-none').empty();
            $('#countryId').val(data ? data.id : '');
            $('#countryName').val(data ? data.name : '');
            $('#countryCode').val(data ? data.country_code : '');
            $('#countryDialCode').val(data ? data.dial_code : '');
            $('#countryIsActive').prop('checked', data ? !!data.is_active : true);
            $('#countryModalLabel').text(data ? 'Edit Country' : 'Add Country');
            $('#saveCountryBtn').text(data ? 'Update' : 'Save');
        }

        function resetCityForm(data = null) {
            $('#cityForm')[0].reset();
            $('#cityErrors').addClass('d-none').empty();
            $('#cityId').val(data ? data.id : '');
            $('#cityName').val(data ? data.name : '');
            $('#cityModalLabel').text(data ? 'Edit City' : 'Add City');
            $('#saveCityBtn').text(data ? 'Update' : 'Save');
            loadStateOptions(data ? data.state_id : null);
        }

        function loadStateOptions(selectedId = null) {
            $.get(routes.states.options)
                .done(res => {
                    const select = $('#cityState');
                    select.empty().append('<option value="">-- Select State --</option>');
                    if (res && res.length) {
                        res.forEach(state => {
                            const option = $('<option></option>')
                                .attr('value', state.id)
                                .text(state.name);
                            if (selectedId && state.id == selectedId) {
                                option.attr('selected', 'selected');
                            }
                            select.append(option);
                        });
                    }
                })
                .fail(() => notify('error', 'Unable to load states.'));
        }

        // Open modals
        $('#openCountryModal').on('click', () => { resetCountryForm(); if (countryModal) countryModal.show(); });
        $('#openStateModal').on('click', () => { resetStateForm(); if (stateModal) stateModal.show(); });
        $('#openCityModal').on('click', () => { resetCityForm(); if (cityModal) cityModal.show(); });

        if (countryStatusFilter) {
            countryStatusFilter.addEventListener('change', function () {
                syncCountryStatusLabel();
                if (countryTable) countryTable.ajax.reload(null, false);
            });
        }

        // Edit Country
        $('#countries-table').on('click', '.btn-edit-country', function () {
            if (!countryTable) return;
            const data = getRowData(countryTable, this);
            if (!data) return;
            resetCountryForm(data);
            if (countryModal) countryModal.show();
        });

        // Edit State
        $('#states-table').on('click', '.btn-edit-state', function () {
            const data = getRowData(stateTable, this);
            if (!data) return;
            resetStateForm(data);
            stateModal.show();
        });

        // Edit City
        $('#cities-table').on('click', '.btn-edit-city', function () {
            const data = getRowData(cityTable, this);
            if (!data) return;
            resetCityForm(data);
            $('#cityState').val(data.state_id);
            cityModal.show();
        });

        // Delete State
        $('#states-table').on('click', '.btn-delete-state', function () {
            const data = getRowData(stateTable, this);
            if (!data) return;
            confirmAndDelete('state', data.id, data.name);
        });

        // Delete Country
        $('#countries-table').on('click', '.btn-delete-country', function () {
            if (!countryTable) return;
            const data = getRowData(countryTable, this);
            if (!data) return;
            confirmAndDelete('country', data.id, data.name);
        });

        // Delete City
        $('#cities-table').on('click', '.btn-delete-city', function () {
            const data = getRowData(cityTable, this);
            if (!data) return;
            confirmAndDelete('city', data.id, data.name);
        });

        function confirmAndDelete(kind, id, name) {
            const label = kind === 'country' ? 'country' : (kind === 'state' ? 'state' : 'city');
            const table = kind === 'country' ? countryTable : (kind === 'state' ? stateTable : cityTable);
            const url = kind === 'country' ? routes.countries.destroy(id) : (kind === 'state' ? routes.states.destroy(id) : routes.cities.destroy(id));

            const proceed = () => {
                $.ajax({
                    url,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                })
                    .done(res => {
                        notify('success', res.message || `${label} deleted successfully.`);
                        if (table) table.ajax.reload(null, false);
                        // Reload related tables when needed
                        if (kind === 'state' && cityTable) cityTable.ajax.reload(null, false);
                        if (kind === 'country' && stateTable) stateTable.ajax.reload(null, false);
                    })
                    .fail(xhr => {
                        const message = xhr.responseJSON?.message || `Unable to delete ${label}.`;
                        notify('error', message);
                    });
            };

            if (window.Swal) {
                Swal.fire({
                    title: `Delete ${label}?`,
                    text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then(result => { if (result.isConfirmed) proceed(); });
            } else {
                if (confirm(`Are you sure you want to delete "${name}"?`)) proceed();
            }
        }

        // Submit State Form
        $('#stateForm').on('submit', function (event) {
            event.preventDefault();
            showErrors('#stateErrors', null);
            const id = $('#stateId').val();
            const url = id ? routes.states.update(id) : routes.states.store;
            const method = id ? 'PUT' : 'POST';
            $.ajax({
                url,
                method,
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .done(res => {
                    notify('success', res.message || 'State saved successfully.');
                    stateModal.hide();
                    stateTable.ajax.reload(null, false);
                })
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors;
                    const message = xhr.responseJSON?.message || 'Unable to save state.';
                    showErrors('#stateErrors', errors);
                    if (!errors) notify('error', message);
                });
        });

        // Submit Country Form
        $('#countryForm').on('submit', function (event) {
            event.preventDefault();
            showErrors('#countryErrors', null);
            const id = $('#countryId').val();
            const url = id ? routes.countries.update(id) : routes.countries.store;
            const method = id ? 'PUT' : 'POST';
            const isActive = $('#countryIsActive').is(':checked') ? '1' : '0';
            const formData = $(this).serializeArray().filter(item => item.name !== 'is_active');
            formData.push({ name: 'is_active', value: isActive });
            const data = $.param(formData);
            $.ajax({
                url,
                method,
                data,
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .done(res => {
                    notify('success', res.message || 'Country saved successfully.');
                    if (countryModal) countryModal.hide();
                    if (countryTable) countryTable.ajax.reload(null, false);
                })
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors;
                    const message = xhr.responseJSON?.message || 'Unable to save country.';
                    showErrors('#countryErrors', errors);
                    if (!errors) notify('error', message);
                });
        });

        // Submit City Form
        $('#cityForm').on('submit', function (event) {
            event.preventDefault();
            showErrors('#cityErrors', null);
            const id = $('#cityId').val();
            const url = id ? routes.cities.update(id) : routes.cities.store;
            const method = id ? 'PUT' : 'POST';
            $.ajax({
                url,
                method,
                data: $(this).serialize(),
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
                .done(res => {
                    notify('success', res.message || 'City saved successfully.');
                    cityModal.hide();
                    cityTable.ajax.reload(null, false);
                })
                .fail(xhr => {
                    const errors = xhr.responseJSON?.errors;
                    const message = xhr.responseJSON?.message || 'Unable to save city.';
                    showErrors('#cityErrors', errors);
                    if (!errors) notify('error', message);
                });
        });
    }
})();