	// Unified logo preview logic for Footer Logo and Footer Brand Logo
	document.addEventListener('DOMContentLoaded', function () {
	    function setupLogoPreview(inputId, previewId) {
		var input = document.getElementById(inputId);
		var preview = document.getElementById(previewId);
		if (input && preview) {
		    // Store the original src for fallback
		    if (!preview.getAttribute('data-original-src')) {
			preview.setAttribute('data-original-src', preview.src);
		    }
		    input.addEventListener('change', function (event) {
			const file = event.target.files[0];
			if (file) {
			    const reader = new FileReader();
			    reader.onload = function (e) {
				preview.src = e.target.result;
				preview.style.display = '';
			    };
			    reader.readAsDataURL(file);
			} else {
			    // If no file selected, revert to original server image if available
			    const originalSrc = preview.getAttribute('data-original-src');
			    if (originalSrc) {
				preview.src = originalSrc;
				preview.style.display = '';
			    } else {
				preview.src = '';
				preview.style.display = 'none';
			    }
			}
		    });
		}
	    }
	    setupLogoPreview('footer_logo', 'footer_logo_preview');
	    setupLogoPreview('footer_brand_logo', 'footer_brand_logo_preview');
	});

// Helper function to show SweetAlert notifications
function showAlert(message, type = 'success') {
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

// Tour Edit Form AJAX Submission using form ID
document.addEventListener('DOMContentLoaded', function () {
	const tourForm = document.getElementById('tourDetailForm');
	
	if (!tourForm) return;
	
	// Prevent multiple submissions
	let isSubmitting = false;
	
	tourForm.addEventListener('submit', function(e) {
		e.preventDefault();
		
		// Prevent double submission
		if (isSubmitting) {
			return false;
		}
		
		// Validate form
		if (!tourForm.checkValidity()) {
			tourForm.classList.add('was-validated');
			return false;
		}
		
		// Custom validation: Check if Credentials Required is checked
		const isCredentialsRequired = document.getElementById('is_credentials');
		if (isCredentialsRequired && isCredentialsRequired.checked) {
			const credentials = document.querySelectorAll('#credentials-container .credential-row');
			if (credentials.length === 0) {
				Swal.fire({
					icon: 'error',
					title: 'Validation Error',
					text: 'Please add at least one credential (Username and Password) when Credentials Required is enabled.',
					confirmButtonColor: '#dc3545'
				});
				return false;
			}
			
			// Check if all credentials have username and password
			let hasValidCredential = false;
			credentials.forEach(row => {
				const username = row.querySelector('input[name*="[user_name]"]')?.value?.trim();
				const password = row.querySelector('input[name*="[password]"]')?.value?.trim();
				if (username && password) {
					hasValidCredential = true;
				}
			});
			
			if (!hasValidCredential) {
				Swal.fire({
					icon: 'error',
					title: 'Validation Error',
					text: 'Each credential must have both Username and Password filled in.',
					confirmButtonColor: '#dc3545'
				});
				return false;
			}
		}
		
		// Custom validation: Check if Is Hosted is checked
		const isHostedCheckbox = document.getElementById('is_hosted');
		if (isHostedCheckbox && isHostedCheckbox.checked) {
			const hostedLink = document.getElementById('hosted_link')?.value?.trim();
			if (!hostedLink) {
				Swal.fire({
					icon: 'error',
					title: 'Validation Error',
					text: 'Hosted Link is required when Is Hosted is enabled.',
					confirmButtonColor: '#dc3545'
				});
				return false;
			}
		}
		
		// Get submit button and store original text
		const submitBtn = tourForm.querySelector('button[type="submit"]');
		const originalBtnText = submitBtn?.innerHTML || '';
		
		// Set submitting flag
		isSubmitting = true;
		
		// Show loading state
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Updating...';
		}
		
		// Submit via AJAX
		fetch(tourForm.action, {
			method: 'POST',
			body: new FormData(tourForm),
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
				tourForm.classList.remove('was-validated');
				tourForm.querySelectorAll('.form-control, .form-select, textarea').forEach(field => {
					field.classList.remove('is-valid', 'is-invalid');
				});
				
				showAlert(data.message || 'Tour updated successfully!', 'success');
			} else {
				showAlert(data.message || 'Failed to update tour', 'error');
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
			
			// Show error alert
			showAlert('An error occurred while updating the tour', 'error');
		});
	});
});

// Sidebar Footer Link Show logic
document.addEventListener('DOMContentLoaded', function () {
	var linkShowSelect = document.getElementById('sidebar_footer_link_show');
	var sidebarFooterFields = document.getElementById('sidebar-footer-fields');
	if (linkShowSelect && sidebarFooterFields) {
		function toggleSidebarFooterFields() {
			if (linkShowSelect.value === '1') {
				sidebarFooterFields.style.display = '';
			} else {
				sidebarFooterFields.style.display = 'none';
			}
		}
		linkShowSelect.addEventListener('change', toggleSidebarFooterFields);
		toggleSidebarFooterFields(); // Initial state
	}

	// Sidebar logo preview
	var sidebarLogoInput = document.getElementById('custom_logo_sidebar');
	var sidebarLogoPreview = document.getElementById('sidebar_logo_preview');
	if (sidebarLogoInput && sidebarLogoPreview) {
		sidebarLogoInput.addEventListener('change', function (event) {
			const file = event.target.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function (e) {
					sidebarLogoPreview.src = e.target.result;
					sidebarLogoPreview.style.display = '';
				};
				reader.readAsDataURL(file);
			} else {
				sidebarLogoPreview.src = '';
				sidebarLogoPreview.style.display = 'none';
			}
		});
	}
});
