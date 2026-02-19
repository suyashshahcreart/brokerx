// Toggle password visibility
function setupPasswordToggle(toggleBtnId, inputId, iconId) {
    const toggleBtn = document.getElementById(toggleBtnId);
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (toggleBtn && input && icon) {
        toggleBtn.addEventListener('click', function () {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            icon.classList.toggle('ri-eye-off-line');
            icon.classList.toggle('ri-eye-line');
        });
    }
}

// Password Strength Checker
function checkPasswordStrength(password) {
    let strength = 0;
    const checks = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
    };

    // Update requirement indicators
    updateRequirement('reqLength', checks.length);
    updateRequirement('reqUppercase', checks.uppercase);
    updateRequirement('reqLowercase', checks.lowercase);
    updateRequirement('reqNumber', checks.number);
    updateRequirement('reqSpecial', checks.special);

    // Calculate strength
    Object.values(checks).forEach(check => {
        if (check) strength++;
    });

    // Update strength bar and text
    const strengthIndicator = document.getElementById('strengthIndicator');
    const strengthText = document.getElementById('strengthText');

    const strengthLevels = [
        { width: 0, text: '', color: '#6c757d' },
        { width: 20, text: 'Very Weak', color: '#dc3545' },
        { width: 40, text: 'Weak', color: '#fd7e14' },
        { width: 60, text: 'Fair', color: '#ffc107' },
        { width: 80, text: 'Good', color: '#17a2b8' },
        { width: 100, text: 'Strong', color: '#28a745' }
    ];

    const level = strengthLevels[strength];
    strengthIndicator.style.width = level.width + '%';
    strengthIndicator.style.background = level.color;
    strengthText.textContent = level.text;
    strengthText.style.color = level.color;

    return strength === 5;
}

function updateRequirement(reqId, met) {
    const req = document.getElementById(reqId);
    if (req) {
        const icon = req.querySelector('i');
        if (met) {
            req.classList.add('met');
            icon.classList.remove('ri-close-circle-line');
            icon.classList.add('ri-check-circle-line');
        } else {
            req.classList.remove('met');
            icon.classList.remove('ri-check-circle-line');
            icon.classList.add('ri-close-circle-line');
        }
    }
}

// Enable/disable submit button based on match state
function updateSubmitButton(isMatch) {
    const btn = document.getElementById('updatePasswordBtn');
    if (!btn) return;
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const shouldDisable = !(isMatch && password.length > 0 && confirmation.length > 0);
    btn.disabled = shouldDisable;
    btn.classList.toggle('disabled', shouldDisable);
}

// Validate password confirmation
function validatePasswordConfirmation() {
    const password = document.getElementById('password').value;
    const confirmation = document.getElementById('password_confirmation').value;
    const confirmField = document.getElementById('password_confirmation');
    const matchText = document.getElementById('confirmPasswordMatch');

    if (confirmation.length === 0) {
        matchText.textContent = '';
        confirmField.classList.remove('is-invalid', 'is-valid');
        updateSubmitButton(false);
        return false;
    }

    const isMatch = password === confirmation;
    if (isMatch) {
        confirmField.classList.remove('is-invalid');
        confirmField.classList.add('is-valid');
        matchText.innerHTML = '<i class="ri-check-circle-line text-success me-1"></i> <span class="text-success">Passwords match!</span>';
    } else {
        confirmField.classList.remove('is-valid');
        confirmField.classList.add('is-invalid');
        matchText.innerHTML = '<i class="ri-close-circle-line text-danger me-1"></i> <span class="text-danger">Passwords do not match!</span>';
    }

    updateSubmitButton(isMatch);
    return isMatch;
}

// Setup all password toggles
setupPasswordToggle('toggleCurrentPassword', 'current_password', 'toggleCurrentPasswordIcon');
setupPasswordToggle('togglePassword', 'password', 'togglePasswordIcon');
setupPasswordToggle('togglePasswordConfirmation', 'password_confirmation', 'togglePasswordConfirmationIcon');

// Add event listeners for real-time validation
document.getElementById('password').addEventListener('input', function () {
    checkPasswordStrength(this.value);
    validatePasswordConfirmation();
});

document.getElementById('password_confirmation').addEventListener('input', function () {
    validatePasswordConfirmation();
});

// Initial check on page load
document.addEventListener('DOMContentLoaded', function () {
    const password = document.getElementById('password').value;
    updateSubmitButton(false);
    if (password) {
        checkPasswordStrength(password);
        validatePasswordConfirmation();
    }
});