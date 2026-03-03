// CSS Import: CSS for Settings page
import '../../css/pages/setting-index.css';

// Helper function to show SweetAlert notifications
function showContactAlert(message, type = 'success') {
    if (type === 'success') {
        Swal.fire({
            icon: 'success',
            text: message,
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            padding: '0',
            timerProgressBar: true,
            customClass: {
                popup: 'alert alert-success alert-dismissible fade show'
            }
        });
    } else {
        Swal.fire({
            icon: 'error',
            text: message,
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            padding: '0',
            timerProgressBar: true,
            customClass: {
                popup: 'alert alert-danger alert-dismissible fade show'
            }
        });
    }
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
        if (error?.message) {
            return error.message;
        }

        if (error?.errors && typeof error.errors === 'object') {
            const firstErrorKey = Object.keys(error.errors)[0];
            if (firstErrorKey && Array.isArray(error.errors[firstErrorKey]) && error.errors[firstErrorKey].length > 0) {
                return error.errors[firstErrorKey][0];
            }
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
                return false;
            }

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
                    showContactAlert(data.message || successMessage, 'success');

                    if (typeof afterSuccess === 'function') {
                        afterSuccess(form, data);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    showContactAlert(resolveErrorMessage(error, errorMessage), 'error');
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

    const initTabForms = function () {
        submitFormAjax(document.querySelector('#loaderConfigTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Loader configuration updated successfully!',
            errorMessage: 'An error occurred while updating loader configuration. Please try again.',
        });

        submitFormAjax(document.querySelector('#languageTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Tour settings updated successfully!',
            errorMessage: 'An error occurred while updating language settings. Please try again.',
        });

        submitFormAjax(document.querySelector('#sidebarTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Tour details updated successfully!',
            errorMessage: 'An error occurred while updating sidebar section. Please try again.',
        });

        submitFormAjax(document.querySelector('#bottomTopTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Tour details updated successfully!',
            errorMessage: 'An error occurred while updating bottom top section. Please try again.',
        });

        submitFormAjax(document.querySelector('#bottomPropertyTabUpdateForm'), {
            loadingText: 'Updating...',
            successMessage: 'Tour details updated successfully!',
            errorMessage: 'An error occurred while updating property details. Please try again.',
        });
    };

    persistPillState();
    initBasicInfoFields();
    initTabForms();
});
