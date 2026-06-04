// CSS Import: CSS for Settings page
import '../../css/pages/setting-index.css';
import { syncTourLanguageTabs } from '../utils/tour-language-tabs';

window.syncTourLanguageTabs = syncTourLanguageTabs;

const TOAST_ICONS = {
    success: 'success',
    error: 'error',
    warning: 'warning',
    info: 'info',
};

const TOAST_CLASSES = {
    success: 'alert alert-success alert-dismissible fade show',
    error: 'alert alert-danger alert-dismissible fade show',
    warning: 'alert alert-warning alert-dismissible fade show',
    info: 'alert alert-info alert-dismissible fade show',
};

/** SweetAlert2 toast (no page reload). */
function showTourToast(message, type = 'success') {
    if (typeof Swal === 'undefined') {
        console[type === 'error' ? 'error' : 'log'](message);
        return;
    }

    const toastType = TOAST_ICONS[type] || 'info';
    const timer = type === 'error' ? 5000 : 3500;

    Swal.fire({
        icon: toastType,
        text: message,
        timer,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
        padding: '0.5rem',
        timerProgressBar: true,
        customClass: {
            popup: TOAST_CLASSES[type] || TOAST_CLASSES.info,
        },
    });
}

function syncTinyMceInForm(form) {
    if (!form || typeof tinymce === 'undefined') {
        return;
    }

    if (typeof tinymce.triggerSave === 'function') {
        tinymce.triggerSave();
        return;
    }

    form.querySelectorAll('textarea.editor').forEach((textarea) => {
        const editor = textarea.id ? tinymce.get(textarea.id) : null;
        if (editor) {
            editor.save();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const submitState = new WeakMap();

    const persistPillState = function () {
        const pillLinks = document.querySelectorAll('#vl-pills-tab [data-bs-toggle="pill"]');
        if (pillLinks.length === 0 || !window.bootstrap || !window.localStorage) {
            return;
        }

        const savedPill = localStorage.getItem('tourTestingActivePill');
        if (savedPill) {
            const savedPillTrigger = document.querySelector(`#vl-pills-tab [href="${savedPill}"]`);
            if (savedPillTrigger) {
                new bootstrap.Tab(savedPillTrigger).show();
            }
        }

        pillLinks.forEach((link) => {
            link.addEventListener('shown.bs.tab', function () {
                const href = link.getAttribute('href');
                if (href) {
                    localStorage.setItem('tourTestingActivePill', href);
                }
            });
        });
    };

    const parseAjaxResponse = async function (response) {
        let data = {};
        try {
            data = await response.json();
        } catch (e) {
            data = {};
        }

        if (!response.ok || !data.success) {
            throw data;
        }

        return data;
    };

    const resolveErrorMessage = function (error, fallbackMessage) {
        if (error?.errors && typeof error.errors === 'object') {
            const messages = [];
            Object.values(error.errors).forEach((value) => {
                if (Array.isArray(value)) {
                    value.forEach((msg) => {
                        if (msg) {
                            messages.push(String(msg));
                        }
                    });
                } else if (value) {
                    messages.push(String(value));
                }
            });

            if (messages.length > 0) {
                const max = 3;
                const shown = messages.slice(0, max).join(' ');
                return messages.length > max ? `${shown} (+${messages.length - max} more)` : shown;
            }
        }

        if (error?.message && error.message !== 'The given data was invalid.') {
            return error.message;
        }

        return fallbackMessage;
    };

    const submitFormAjax = function (form, options = {}) {
        if (!form) {
            return;
        }

        const {
            loadingText = 'Updating...',
            successMessage = 'Updated successfully!',
            errorMessage = 'Something went wrong. Please try again.',
            beforeSubmit = null,
            afterSuccess = null,
        } = options;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (submitState.get(form)) {
                return false;
            }

            if (typeof beforeSubmit === 'function') {
                const shouldContinue = beforeSubmit(form);
                if (shouldContinue === false) {
                    return false;
                }
            }

            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                showTourToast('Please correct the highlighted required fields.', 'warning');
                return false;
            }

            syncTinyMceInForm(form);

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn?.innerHTML || '';
            submitState.set(form, true);

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> ${loadingText}`;
            }

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json'
                }
            })
                .then(parseAjaxResponse)
                .then((data) => {
                    form.classList.remove('was-validated');
                    showTourToast(data.message || successMessage, 'success');

                    if (typeof afterSuccess === 'function') {
                        afterSuccess(form, data);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    showTourToast(resolveErrorMessage(error, errorMessage), 'error');
                })
                .finally(() => {
                    submitState.set(form, false);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
        });
    };

    const initBasicInfoFields = function () {
        const basicForm = document.querySelector('#basicInfoTabUpdate');
        if (!basicForm) {
            return;
        }

        const isActive = basicForm.querySelector('#is_active');
        const isCredentials = basicForm.querySelector('#testing_is_credentials');
        const isHosted = basicForm.querySelector('#testing_is_hosted');

        const credentialsRequiredField = basicForm.querySelector('#testing-credentials-required-field');
        const mobileValidationField = basicForm.querySelector('#mobile-validation-field');
        const isHostedField = basicForm.querySelector('#testing-is-hosted-field');
        const hostedLinkContainer = basicForm.querySelector('#testing-hosted-link-container');
        const hostedLinkInput = basicForm.querySelector('#testing_hosted_link');
        const credentialsSection = basicForm.querySelector('#testing-credentials-section');
        const credentialsContainer = basicForm.querySelector('#testing-credentials-container');
        const addCredentialBtn = basicForm.querySelector('#testing-add-credential-btn');

        const setCredentialsInputsState = function (enabled) {
            if (!credentialsSection) {
                return;
            }

            const credentialInputs = credentialsSection.querySelectorAll('input, select, textarea, button');
            credentialInputs.forEach((element) => {
                if (element.id === 'testing-add-credential-btn' || element.classList.contains('remove-credential')) {
                    element.disabled = !enabled;
                }

                if (element.name && (element.name.includes('[user_name]') || element.name.includes('[password]'))) {
                    element.required = enabled;
                }

                if ('disabled' in element && element.id !== 'testing-add-credential-btn' && !element.classList.contains('remove-credential')) {
                    element.disabled = !enabled;
                }
            });
        };

        const toggleBasicFields = function () {
            const active = isActive ? isActive.checked : true;
            const credentialsChecked = isCredentials ? isCredentials.checked : false;
            const hostedChecked = isHosted ? isHosted.checked : false;

            if (credentialsRequiredField) {
                credentialsRequiredField.style.display = active ? '' : 'none';
            }
            if (mobileValidationField) {
                mobileValidationField.style.display = active ? '' : 'none';
            }
            if (isHostedField) {
                isHostedField.style.display = active ? '' : 'none';
            }

            if (hostedLinkContainer) {
                const showHostedLink = active && hostedChecked;
                hostedLinkContainer.classList.toggle('d-none', !showHostedLink);
                if (hostedLinkInput) {
                    hostedLinkInput.required = showHostedLink;
                    hostedLinkInput.disabled = !showHostedLink;
                    if (!showHostedLink) {
                        hostedLinkInput.classList.remove('is-valid', 'is-invalid');
                    }
                }
            }

            if (credentialsSection) {
                const showCredentials = active && credentialsChecked;
                credentialsSection.classList.toggle('d-none', !showCredentials);
                setCredentialsInputsState(showCredentials);
            }
        };

        if (isActive) {
            isActive.addEventListener('change', toggleBasicFields);
        }
        if (isCredentials) {
            isCredentials.addEventListener('change', toggleBasicFields);
        }
        if (isHosted) {
            isHosted.addEventListener('change', toggleBasicFields);
        }
        toggleBasicFields();

        if (addCredentialBtn && credentialsContainer) {
            let credentialIndex = credentialsContainer.querySelectorAll('.credential-row').length;

            addCredentialBtn.addEventListener('click', function () {
                const row = document.createElement('div');
                row.className = 'credential-row row mb-2 align-items-end';
                row.innerHTML = `
                    <div class="col-md-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="credentials[${credentialIndex}][user_name]" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Password</label>
                        <input type="text" name="credentials[${credentialIndex}][password]" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="credentials[${credentialIndex}][is_active]" class="form-select">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-credential"><i class="ri-delete-bin-line"></i></button>
                    </div>
                `;

                credentialsContainer.appendChild(row);
                credentialIndex++;

                if (isCredentials && !isCredentials.checked) {
                    toggleBasicFields();
                }
            });

            credentialsContainer.addEventListener('click', function (event) {
                if (event.target.closest('.remove-credential')) {
                    event.target.closest('.credential-row')?.remove();
                }
            });
        }

        submitFormAjax(basicForm, {
            loadingText: 'Updating...',
            successMessage: 'Tour basic information updated successfully!',
            errorMessage: 'An error occurred while updating tour basic information. Please try again.',
        });
    };

    const syncBottomMarkLanguageTabs = function () {
        syncTourLanguageTabs();
    };

    const initTabForms = function () {
        submitFormAjax(document.querySelector('#tourContactInfoTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Tour contact information updated successfully!',
            errorMessage: 'An error occurred while updating tour contact information. Please try again.',
        });

        submitFormAjax(document.querySelector('#loaderConfigTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Loader configuration updated successfully!',
            errorMessage: 'An error occurred while updating loader configuration. Please try again.',
        });

        submitFormAjax(document.querySelector('#languageTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Language settings updated successfully!',
            errorMessage: 'An error occurred while updating language settings. Please try again.',
            beforeSubmit: () => {
                if (typeof window.prepareLanguageTabFormBeforeSubmit === 'function') {
                    window.prepareLanguageTabFormBeforeSubmit();
                }
            },
            afterSuccess: (data) => {
                if (data?.tour?.enable_language) {
                    window.enabledLanguages = data.tour.enable_language;
                }
                if (data?.tour?.language_slot_order && window.tourLanguageConfig) {
                    window.tourLanguageConfig.languageSlotOrder = data.tour.language_slot_order;
                    window.tourLanguageConfig.slots = data.tour.language_slot_order;
                }
                if (data?.tour?.language_display && window.tourLanguageConfig) {
                    window.tourLanguageConfig.languageDisplay = data.tour.language_display;
                }
                syncTourLanguageTabs();
            },
        });

        submitFormAjax(document.querySelector('#sidebarConfigTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Sidebar configuration updated successfully!',
            errorMessage: 'An error occurred while updating sidebar configuration. Please try again.',
            afterSuccess: (form, data) => {
                const logoUrl = data?.sidebar_config_logo_url;
                if (logoUrl) {
                    const preview = document.getElementById('sidebar_config_logo_preview');
                    if (preview) {
                        preview.src = logoUrl;
                        preview.style.display = '';
                    }
                }
            },
        });

        submitFormAjax(document.querySelector('#attachmentsTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Tour attachments updated successfully!',
            errorMessage: 'An error occurred while updating attachments. Please try again.',
        });

        submitFormAjax(document.querySelector('#bottomTopTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Bottom top section updated successfully!',
            errorMessage: 'An error occurred while updating bottom top section. Please try again.',
            afterSuccess: (form, data) => {
                const preview = document.getElementById('footer_logo_preview');
                if (!preview) {
                    return;
                }

                if (data?.tour?.footer_logo) {
                    preview.src = data.tour.footer_logo;
                    preview.dataset.originalSrc = data.tour.footer_logo;
                    preview.style.display = '';
                    return;
                }

                const originalSrc = preview.dataset.originalSrc;
                if (originalSrc) {
                    preview.src = originalSrc;
                    preview.style.display = '';
                }
            },
        });

        submitFormAjax(document.querySelector('#bottomPropertyTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Property details updated successfully!',
            errorMessage: 'An error occurred while updating property details. Please try again.',
        });

        submitFormAjax(document.querySelector('#tourBookmarkTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Tour bookmark updated successfully!',
            errorMessage: 'An error occurred while updating tour bookmark. Please try again.',
        });

        submitFormAjax(document.querySelector('#sidebarLinksForm'), {
            loadingText: 'Updating...',
            successMessage: 'Sidebar links updated successfully!',
            errorMessage: 'An error occurred while updating sidebar links. Please try again.',
            afterSuccess: (form, data) => {
                if (data?.tour?.sidebar_links) {
                    window.sidebarLinksData = data.tour.sidebar_links;
                }
            },
        });

        submitFormAjax(document.querySelector('#userDetailsTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'User details updated successfully!',
            errorMessage: 'An error occurred while updating user details. Please try again.',
            afterSuccess: (form, data) => {
                if (data?.tour?.user_details) {
                    const container = document.getElementById('userDetailsContainer');
                    if (container) {
                        container.setAttribute('data-userdetails-data', JSON.stringify(data.tour.user_details));
                    }
                }
            },
        });

        submitFormAjax(document.querySelector('#userStarsTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'User stars updated successfully!',
            errorMessage: 'An error occurred while updating user stars. Please try again.',
            afterSuccess: (form, data) => {
                const stars = data?.tour?.user_star?.stars;
                if (stars) {
                    const container = document.getElementById('userStarsContainer');
                    if (container) {
                        container.setAttribute('data-user-stars', JSON.stringify(stars));
                    }
                }
            },
        });
    };

    persistPillState();
    initBasicInfoFields();
    initTabForms();

    const enabledLanguageInputs = document.querySelectorAll('#languageTabUpdateForm input[name="enable_language[]"]');
    enabledLanguageInputs.forEach((input) => {
        input.addEventListener('change', syncBottomMarkLanguageTabs);
    });

    syncBottomMarkLanguageTabs();
});

window.showTourToast = showTourToast;
