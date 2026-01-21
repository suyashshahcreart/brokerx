(function () {
  'use strict';
  
  // Wait for DOM to be fully loaded
  function initLogin() {
    var formEl = document.querySelector('form.authentication-form');
    var identifier = document.getElementById('login-identifier');
    var passwordBlock = document.getElementById('password-block');
    var passwordInput = document.getElementById('login-password');
    var otpBlock = document.getElementById('otp-login-block');
    var toggleOtpBtn = document.getElementById('login-toggle-otp');
    var togglePasswordBtn = document.getElementById('login-toggle-password');
    var otpCodeInput = document.getElementById('login-otp-code');
    var otpText = document.getElementById('login-otp-text');
    var otpVerifiedInput = document.getElementById('login-otp-verified');
    var submitBtn = document.getElementById('btn-submit-login');
    var changeIdentifierBtn = document.getElementById('login-change-identifier');
    var togglePassword = document.getElementById('togglePassword');
    var togglePasswordIcon = document.getElementById('togglePasswordIcon');

    var csrf = document.querySelector('input[name="_token"]');
    var otpSendUrl = formEl ? formEl.getAttribute('data-otp-send') : null;
    var otpVerifyUrl = formEl ? formEl.getAttribute('data-otp-verify') : null;
    var emailOtpSendUrl = formEl ? formEl.getAttribute('data-email-otp-send') : null;
    var emailOtpVerifyUrl = formEl ? formEl.getAttribute('data-email-otp-verify') : null;

    // Form validation state
    var validationState = {
      identifier: false,
      password: false
    };

    // Password visibility toggle
    if (togglePassword && passwordInput && togglePasswordIcon) {
      togglePassword.addEventListener('click', function (e) {
        e.preventDefault();
        var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        if (type === 'text') {
          togglePasswordIcon.classList.remove('bx-hide');
          togglePasswordIcon.classList.add('bx-show');
          togglePassword.setAttribute('title', 'Hide Password');
        } else {
          togglePasswordIcon.classList.remove('bx-show');
          togglePasswordIcon.classList.add('bx-hide');
          togglePassword.setAttribute('title', 'Show Password');
        }
      });
    }

  function showOtpMode() {
    if (passwordBlock) passwordBlock.style.display = 'none';
    if (otpBlock) otpBlock.style.display = '';
    if (otpCodeInput) otpCodeInput.value = '';
    if (otpText) { otpText.textContent = ''; otpText.className = 'form-text d-flex align-items-center gap-1 mt-1'; }
    if (otpVerifiedInput) otpVerifiedInput.value = '0';
    if (identifier) identifier.readOnly = true;
    
    // Auto-send OTP if identifier is valid
    autoSendOtp();
  }

  function showPasswordMode() {
    if (otpBlock) otpBlock.style.display = 'none';
    if (passwordBlock) passwordBlock.style.display = '';
    if (otpVerifiedInput) otpVerifiedInput.value = '0';
  }

  if (toggleOtpBtn) toggleOtpBtn.addEventListener('click', showOtpMode);
  if (togglePasswordBtn) togglePasswordBtn.addEventListener('click', showPasswordMode);
  if (changeIdentifierBtn) changeIdentifierBtn.addEventListener('click', function () {
    if (!identifier) return;
    // Switch back to password mode so user can click 'Verify with OTP' again
    showPasswordMode();
    identifier.readOnly = false;
    identifier.focus();
    try { identifier.select(); } catch (e) {}
    if (otpCodeInput) otpCodeInput.value = '';
    if (otpVerifiedInput) otpVerifiedInput.value = '0';
    if (otpText) {
      otpText.textContent = '';
      otpText.className = 'form-text d-flex align-items-center gap-1 mt-1';
    }
  });

  function isMobile(value) {
    return /^\+?\d{8,20}$/.test(value || '');
  }
  function isEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value || '');
  }

  function autoSendOtp() {
    if (!identifier) return;
    var idv = (identifier.value || '').trim();
    var useMobile = isMobile(idv);
    var useEmail = isEmail(idv);
    
    if (!useMobile && !useEmail) {
      if (otpText) {
        otpText.innerHTML = "<i class='bx bx-info-circle me-1'></i>Please enter a valid email or mobile number first";
        otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-warning';
      }
      if (identifier) identifier.focus();
      return;
    }
    
    // Valid identifier found, send OTP automatically
    if (otpText) {
      otpText.innerHTML = "<i class='bx bx-loader-alt bx-spin me-1'></i>Sending OTP...";
      otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-info';
    }
    
    var url = useMobile ? otpSendUrl : emailOtpSendUrl;
    var payload = useMobile ? { mobile: idv } : { email: idv };
    
    fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.value : '' },
      body: JSON.stringify(payload)
    })
      .then(function (res) { return res.json().then(function (data) { return { status: res.status, data: data }; }); })
      .then(function (resp) {
        var ok = resp.status >= 200 && resp.status < 300 && resp.data && resp.data.ok;
        if (ok) {
          if (otpText) {
            otpText.innerHTML = "<i class='bx bx-check-circle me-1'></i>OTP sent successfully";
            otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-success';
          }
          if (otpCodeInput) { otpCodeInput.value = ''; otpCodeInput.focus(); }
          if (identifier) { identifier.readOnly = true; }
        } else {
          var msg = (resp.data && resp.data.message) ? resp.data.message : 'Failed to send OTP';
          if (otpText) {
            otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>" + msg;
            otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
          }
        }
      })
      .catch(function () {
        if (otpText) {
          otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Network error. Try again.";
          otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        }
      });
  }

  // No client-side OTP verification now; the server will handle OTP check on form submit

  // === ENHANCED VALIDATION AND UX FEATURES ===

  // Validate identifier field
  function validateIdentifier() {
    if (!identifier) return false;
    
    var value = identifier.value.trim();
    var isValid = isEmail(value) || isMobile(value);
    var errorEl = document.getElementById('identifier-error');
    
    if (!value) {
      setFieldError(identifier, errorEl, 'Email or mobile number is required');
      validationState.identifier = false;
      return false;
    }
    
    if (!isValid) {
      setFieldError(identifier, errorEl, 'Please enter a valid email address or mobile number');
      validationState.identifier = false;
      return false;
    }
    
    setFieldSuccess(identifier, errorEl);
    validationState.identifier = true;
    return true;
  }

  // Validate password field
  function validatePassword() {
    if (!passwordInput) return false;
    
    var value = passwordInput.value;
    var errorEl = document.getElementById('password-error');
    
    if (!value || value.length < 1) {
      setFieldError(passwordInput, errorEl, 'Password is required');
      validationState.password = false;
      return false;
    }
    
    if (value.length < 6) {
      setFieldError(passwordInput, errorEl, 'Password must be at least 6 characters');
      validationState.password = false;
      return false;
    }
    
    setFieldSuccess(passwordInput, errorEl);
    validationState.password = true;
    return true;
  }

  // Set field error state
  function setFieldError(field, errorEl, message) {
    if (field) {
      field.classList.add('is-invalid');
      field.classList.remove('is-valid');
    }
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.style.display = 'block';
    }
  }

  // Set field success state
  function setFieldSuccess(field, errorEl) {
    if (field) {
      field.classList.remove('is-invalid');
      field.classList.add('is-valid');
    }
    if (errorEl) {
      errorEl.style.display = 'none';
    }
  }

  // Real-time validation on input
  if (identifier) {
    identifier.addEventListener('blur', validateIdentifier);
    identifier.addEventListener('input', function () {
      if (identifier.classList.contains('is-invalid')) {
        validateIdentifier();
      }
    });
  }

  if (passwordInput) {
    passwordInput.addEventListener('blur', validatePassword);
    passwordInput.addEventListener('input', function () {
      if (passwordInput.classList.contains('is-invalid')) {
        validatePassword();
      }
    });
  }

  // Form submission with validation
  if (formEl) {
    formEl.addEventListener('submit', function (e) {
      var identifierValid = validateIdentifier();
      var passwordValid = otpBlock && otpBlock.style.display !== 'none' ? true : validatePassword();
      
      if (!identifierValid || !passwordValid) {
        e.preventDefault();
        
        // Show validation error message
        showAlert('Please correct the errors before submitting', 'danger');
        
        // Focus first invalid field
        if (!identifierValid && identifier) {
          identifier.focus();
        } else if (!passwordValid && passwordInput) {
          passwordInput.focus();
        }
        
        return false;
      }

      // Show loading state
      setLoadingState(true);
    });
  }

  // Show alert message
  function showAlert(message, type) {
    type = type || 'danger';
    var alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = '<i class="bx bx-error-circle me-2"></i>' + message + 
      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    
    if (formEl) {
      formEl.insertBefore(alertDiv, formEl.firstChild);
      
      // Auto-dismiss after 5 seconds
      setTimeout(function () {
        alertDiv.classList.remove('show');
        setTimeout(function () {
          alertDiv.remove();
        }, 150);
      }, 5000);
    }
  }

  // Set loading state on submit button
  function setLoadingState(isLoading) {
    if (!submitBtn) return;
    
    var btnText = submitBtn.querySelector('.btn-text');
    var spinner = submitBtn.querySelector('.spinner-border');
    
    if (isLoading) {
      submitBtn.disabled = true;
      if (btnText) btnText.textContent = 'Signing In...';
      if (spinner) spinner.classList.remove('d-none');
    } else {
      submitBtn.disabled = false;
      if (btnText) btnText.textContent = 'Sign In';
      if (spinner) spinner.classList.add('d-none');
    }
  }

  // Auto-dismiss alerts after page load
  window.addEventListener('load', function () {
    var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function (alert) {
      setTimeout(function () {
        if (alert && alert.classList.contains('show')) {
          var closeBtn = alert.querySelector('.btn-close');
          if (closeBtn) closeBtn.click();
        }
      }, 8000);
    });
  });
  
  } // End of initLogin function

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLogin);
  } else {
    initLogin();
  }
})();


