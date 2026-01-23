document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const identifierInput = document.getElementById('login-identifier');
    const passwordInput = document.getElementById('login-password');
    const submitButton = document.getElementById('btn-submit-login');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

    // Password toggle functionality
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'text') {
                togglePasswordIcon.classList.remove('bx-hide');
                togglePasswordIcon.classList.add('bx-show');
                togglePasswordBtn.setAttribute('title', 'Hide Password');
            } else {
                togglePasswordIcon.classList.remove('bx-show');
                togglePasswordIcon.classList.add('bx-hide');
                togglePasswordBtn.setAttribute('title', 'Show Password');
            }
        });
    }

    // Form validation - disable submit button if fields are empty
    function validateForm() {
        const identifierValue = identifierInput.value.trim();
        const passwordValue = passwordInput.value.trim();
        
        if (identifierValue && passwordValue) {
            submitButton.disabled = false;
            submitButton.classList.remove('disabled');
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
        }
    }

    // Validate on input
    if (identifierInput && passwordInput && submitButton) {
        // Initial validation
        validateForm();
        
        // Add event listeners
        identifierInput.addEventListener('input', validateForm);
        passwordInput.addEventListener('input', validateForm);
        identifierInput.addEventListener('blur', validateForm);
        passwordInput.addEventListener('blur', validateForm);
    }

    // Form submission handler
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const identifierValue = identifierInput.value.trim();
            const passwordValue = passwordInput.value.trim();
            
            if (!identifierValue || !passwordValue) {
                e.preventDefault();
                
                if (!identifierValue) {
                    identifierInput.classList.add('is-invalid');
                }
                if (!passwordValue) {
                    passwordInput.classList.add('is-invalid');
                }
                return false;
            }
            
            // Show loading state
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            if (btnText) btnText.textContent = 'Signing in...';
            if (spinner) spinner.classList.remove('d-none');
            submitButton.disabled = true;
        });
    }

    // Remove invalid class on input
    if (identifierInput) {
        identifierInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    }
});
