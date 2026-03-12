// Helper function to show SweetAlert notifications
function showTourSettingsAlert(message, type = 'success') {
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

// Tour Settings Form AJAX Submission
document.addEventListener('DOMContentLoaded', function () {
    const tourSettingsForm = document.getElementById('tourSettingsForm');

    if (!tourSettingsForm) return;

    // Setup color picker sync for overlay color
    const setupColorPickerSync = (pickerId, inputId) => {
        const picker = document.getElementById(pickerId);
        const input = document.getElementById(inputId);

        if (!picker || !input) return;

        picker.addEventListener('change', function () {
            input.value = this.value;
        });

        input.addEventListener('input', function () {
            if (/^#([0-9A-F]{3}){1,2}$/i.test(this.value)) {
                picker.value = this.value;
            }
        });
    };

    setupColorPickerSync('overlay_bg_color_picker', 'overlay_bg_color');

    // Add Loader Color
    document.getElementById('addLoaderColor')?.addEventListener('click', function (e) {
        e.preventDefault();
        const container = document.getElementById('loaderColorContainer');
        const newRow = document.createElement('div');
        newRow.className = 'col-md-2 loader-color-row';
        newRow.innerHTML = `
				<div class="input-group">
					<span class="input-group-text p-1">
						<input type="color" class="form-control form-control-color loader-color-picker"
							value="#000000"
							onchange="this.parentElement.nextElementSibling.value = this.value">
					</span>
					<input type="text" name="loader_color[]" class="form-control loader-color-input"
                        oninput="this.previousElementSibling.querySelector('input').value = this.value"
						placeholder="#000000" value="#000000">
                    <button type="button" class="btn btn-soft-danger remove-loader-color">
					    <i class="ri-delete-bin-line"></i>
				    </button>
				</div>
		`;
        container.appendChild(newRow);
        setupRemoveButtons();
    });

    // Add Spinner Color
    document.getElementById('addSpinnerColor')?.addEventListener('click', function (e) {
        e.preventDefault();
        const container = document.getElementById('spinnerColorContainer');
        const newCol = document.createElement('div');
        newCol.className = 'col-md-2 spinner-color-row';
        newCol.innerHTML = `
				<div class="input-group">
					<span class="input-group-text p-1">
						<input type="color" class="form-control form-control-color spinner-color-picker"
							value="#000000"
                            oninput="this.previousElementSibling.querySelector('input').value = this.value"
							onchange="this.parentElement.nextElementSibling.value = this.value">
					</span>
					<input type="text" name="spinner_color[]" class="form-control spinner-color-input"
						placeholder="#000000" value="#000000">
                    <button type="button" class="btn btn-soft-danger remove-spinner-color">
					    <i class="ri-delete-bin-line"></i>
				    </button>
				</div>
		`;
        container.appendChild(newCol);
        setupRemoveButtons();
    });

    // Setup remove buttons
    const setupRemoveButtons = () => {
        document.querySelectorAll('.remove-loader-color').forEach(btn => {
            btn.onclick = function (e) {
                e.preventDefault();
                this.closest('.loader-color-row').remove();
            };
        });

        document.querySelectorAll('.remove-spinner-color').forEach(btn => {
            btn.onclick = function (e) {
                e.preventDefault();
                this.closest('.spinner-color-row').remove();
            };
        });
    };

    // Initial setup
    setupRemoveButtons();

    // Prevent multiple submissions
    let isSubmitting = false;

    tourSettingsForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Prevent double submission
        if (isSubmitting) {
            return false;
        }

        // Validate form
        if (!tourSettingsForm.checkValidity()) {
            tourSettingsForm.classList.add('was-validated');
            return false;
        }

        // Validate at least one language is selected
        const selectedLanguages = document.querySelectorAll('input[name="enable_language[]"]:checked');
        if (selectedLanguages.length === 0) {
            showTourSettingsAlert('Please select at least one language.', 'error');
            return false;
        }

        // Get submit button and store original text
        const submitBtn = tourSettingsForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn?.innerHTML || '';

        // Set submitting flag
        isSubmitting = true;

        // Show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Updating...';
        }

        // Submit via AJAX
        fetch(tourSettingsForm.action, {
            method: 'POST',
            body: new FormData(tourSettingsForm),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                // Reset submitting flag
                isSubmitting = false;

                // Reset button state
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }

                // Show SweetAlert notification
                if (data.success) {
                    // Clear all validation classes from form fields
                    tourSettingsForm.classList.remove('was-validated');
                    tourSettingsForm.querySelectorAll('.form-control, .form-select, textarea').forEach(field => {
                        field.classList.remove('is-valid', 'is-invalid');
                    });

                    showTourSettingsAlert(data.message || 'Tour settings updated successfully!', 'success');

                    // Update form values with new data if available
                    if (data.tour) {
                        document.getElementById('default_language').value = data.tour.default_language || '';
                        document.getElementById('overlay_bg_color').value = data.tour.overlay_bg_color || '';
                        document.getElementById('overlay_bg_color_picker').value = data.tour.overlay_bg_color || '#000040';
                        document.getElementById('loader_text').value = data.tour.loader_text || '';
                    }
                } else {
                    showTourSettingsAlert(data.message || 'Failed to update tour settings', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);

                // Reset submitting flag
                isSubmitting = false;

                // Reset button state
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }

                showTourSettingsAlert('An error occurred while updating tour settings. Please try again.', 'error');
            });
    });
});
