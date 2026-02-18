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

// Contact Info Form AJAX Submission
document.addEventListener('DOMContentLoaded', function () {
	const contactForm = document.getElementById('contactInfoForm');
	
	if (!contactForm) return;
	
	// Prevent multiple submissions
	let isSubmitting = false;
	
	contactForm.addEventListener('submit', function(e) {
		e.preventDefault();
		
		// Prevent double submission
		if (isSubmitting) {
			return false;
		}
		
		// Validate form
		if (!contactForm.checkValidity()) {
			contactForm.classList.add('was-validated');
			return false;
		}
		
		// Get submit button and store original text
		const submitBtn = contactForm.querySelector('button[type="submit"]');
		const originalBtnText = submitBtn?.innerHTML || '';
		
		// Set submitting flag
		isSubmitting = true;
		
		// Show loading state
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Updating...';
		}
		
		// Submit via AJAX
		fetch(contactForm.action, {
			method: 'POST',
			body: new FormData(contactForm),
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
				contactForm.classList.remove('was-validated');
				contactForm.querySelectorAll('.form-control, .form-select, textarea').forEach(field => {
					field.classList.remove('is-valid', 'is-invalid');
				});
				
				showContactAlert(data.message || 'Contact information updated successfully!', 'success');
				
				// Update form values with new data if available
				if (data.tour) {
					document.getElementById('contact_google_location').value = data.tour.contact_google_location || '';
					document.getElementById('contact_website').value = data.tour.contact_website || '';
					document.getElementById('contact_email').value = data.tour.contact_email || '';
					document.getElementById('contact_phone_no').value = data.tour.contact_phone_no || '';
					document.getElementById('contact_whatsapp_no').value = data.tour.contact_whatsapp_no || '';
				}
			} else {
				showContactAlert(data.message || 'Failed to update contact information', 'error');
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
			
			showContactAlert('An error occurred while updating contact information. Please try again.', 'error');
		});
	});
});
