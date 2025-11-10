(function () {
  'use strict';

  var formEl = document.querySelector('#user-form');
  if (!formEl) return;

  var csrf = formEl.querySelector('input[name="_token"]');
  var otpSendUrl = formEl.getAttribute('data-otp-send');
  var otpVerifyUrl = formEl.getAttribute('data-otp-verify');
  var emailOtpSendUrl = formEl.getAttribute('data-email-otp-send');
  var emailOtpVerifyUrl = formEl.getAttribute('data-email-otp-verify');
  var emailOtpVerifyUrl = formEl.getAttribute('data-email-otp-verify');

  // ============================================
  // MOBILE VERIFICATION
  // ============================================
  var mobileInput = document.getElementById('mobile');
  var mobileInputGroup = document.getElementById('mobile-input-group');
  var mobileBtn = document.getElementById('btn-verify-mobile');
  var mobileText = document.getElementById('mobile-verify-text');
  var otpBlock = document.getElementById('otp-block');
  var otpCode = document.getElementById('otp-code');
  var otpSubmit = document.getElementById('btn-submit-otp');
  var otpMobileDisplay = document.getElementById('otp-mobile-display');
  var changeMobileBtn = document.getElementById('btn-change-mobile');
  var mobileVerifiedBadge = document.getElementById('mobile-verified-badge');
  var mobileChangeAfter = document.getElementById('mobile-change-after');
  var changeMobileAfterBtn = document.getElementById('btn-change-mobile-after');

  if (mobileInput && mobileBtn && mobileText && otpSendUrl) {
    mobileBtn.addEventListener('click', function () {
      var value = (mobileInput.value || '').trim();
      if (!value || value.length < 8) {
        mobileText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Enter a valid mobile number (min 8 characters)";
        mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        mobileInput.classList.add('is-invalid');
        return;
      }
      if (!/^\+?\d{8,20}$/.test(value)) {
        mobileText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Mobile number can only contain digits and optional + prefix";
        mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        mobileInput.classList.add('is-invalid');
        return;
      }
      mobileInput.classList.remove('is-invalid');
      mobileBtn.disabled = true;
      mobileBtn.textContent = 'Sending...';

      fetch(otpSendUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf ? csrf.value : '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ mobile: value })
      })
        .then(function (res) { return res.json().then(function (data) { return { status: res.status, data: data }; }); })
        .then(function (resp) {
          var ok = resp.status >= 200 && resp.status < 300 && resp.data && resp.data.ok;
          if (ok) {
            mobileText.innerHTML = "<i class='bx bx-check-circle me-1'></i>Verification code sent";
            mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-success';
            if (mobileInputGroup) mobileInputGroup.style.display = 'none';
            if (otpBlock) otpBlock.style.display = '';
            if (otpMobileDisplay) otpMobileDisplay.textContent = value;
            if (otpCode) {
              otpCode.value = '';
              otpCode.focus();
            }
          } else {
            var msg = (resp.data && resp.data.message) ? resp.data.message : 'Failed to send code';
            mobileText.innerHTML = "<i class='bx bx-x-circle me-1'></i>" + msg;
            mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
          }
        })
        .catch(function () {
          mobileText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Network error. Try again.";
          mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        })
        .finally(function () {
          mobileBtn.disabled = false;
          mobileBtn.textContent = 'Verify';
        });
    });
  }

  if (changeMobileBtn && mobileInputGroup && otpBlock) {
    changeMobileBtn.addEventListener('click', function () {
      if (otpBlock) otpBlock.style.display = 'none';
      if (mobileInputGroup) mobileInputGroup.style.display = '';
      if (otpCode) otpCode.value = '';
      if (mobileText) {
        mobileText.textContent = '';
        mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1';
      }
      if (mobileInput) {
        mobileInput.readOnly = false;
        mobileInput.focus();
      }
    });
  }

  if (otpSubmit && mobileInput && otpCode && mobileText && otpVerifyUrl) {
    otpSubmit.addEventListener('click', function () {
      var value = (mobileInput.value || '').trim();
      var code = (otpCode.value || '').trim();
      if (!code || code.length !== 6) {
        mobileText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Enter the 6-digit code";
        mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        otpCode.focus();
        return;
      }
      otpSubmit.disabled = true;
      otpSubmit.textContent = 'Verifying...';

      fetch(otpVerifyUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf ? csrf.value : '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ mobile: value, code: code })
      })
        .then(function (res) { return res.json().then(function (data) { return { status: res.status, data: data }; }); })
        .then(function (resp) {
          var ok = resp.status >= 200 && resp.status < 300 && resp.data && resp.data.ok;
          if (ok) {
            mobileText.innerHTML = "<i class='bx bx-check-circle me-1'></i>Mobile verified successfully!";
            mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-success';
            // Hide OTP UI and show verified badge
            if (otpBlock) otpBlock.style.display = 'none';
            if (mobileInputGroup) mobileInputGroup.style.display = '';
            if (mobileBtn) mobileBtn.style.display = 'none';
            if (otpCode) otpCode.value = '';
            // Lock mobile field
            mobileInput.readOnly = true;
            // Show verified badge
            var badge = document.createElement('span');
            badge.id = 'mobile-verified-badge';
            badge.className = 'input-group-text bg-success text-white';
            badge.innerHTML = "<i class='bx bx-check-circle me-1'></i>Verified";
            mobileInputGroup.appendChild(badge);
            // Show change link
            if (mobileChangeAfter) mobileChangeAfter.classList.remove('d-none');
            
            // Reload page after 2 seconds to refresh state
            setTimeout(function() {
              window.location.reload();
            }, 2000);
          } else {
            var msg = (resp.data && resp.data.message) ? resp.data.message : 'Verification failed';
            mobileText.innerHTML = "<i class='bx bx-x-circle me-1'></i>" + msg;
            mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
          }
        })
        .catch(function () {
          mobileText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Network error. Try again.";
          mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        })
        .finally(function () {
          otpSubmit.disabled = false;
          otpSubmit.textContent = 'Submit OTP';
        });
    });
  }

  if (changeMobileAfterBtn && mobileInputGroup) {
    changeMobileAfterBtn.addEventListener('click', function () {
      var badge = document.getElementById('mobile-verified-badge');
      if (badge) badge.remove();
      if (mobileBtn) mobileBtn.style.display = '';
      if (mobileChangeAfter) mobileChangeAfter.classList.add('d-none');
      if (mobileInput) {
        mobileInput.readOnly = false;
        mobileInput.focus();
      }
      if (mobileText) {
        mobileText.textContent = '';
        mobileText.className = 'form-text d-flex align-items-center gap-1 mt-1';
      }
    });
  }

  // ============================================
  // EMAIL VERIFICATION
  // ============================================
  var emailInput = document.getElementById('email');
  var emailInputGroup = document.getElementById('email-input-group');
  var emailBtn = document.getElementById('btn-verify-email');
  var emailText = document.getElementById('email-verify-text');
  var emailOtpBlock = document.getElementById('email-otp-block');
  var emailOtpCode = document.getElementById('email-otp-code');
  var emailOtpSubmit = document.getElementById('btn-submit-email-otp');
  var otpEmailDisplay = document.getElementById('otp-email-display');
  var changeEmailBtn = document.getElementById('btn-change-email');
  var emailVerifiedBadge = document.getElementById('email-verified-badge');
  var emailChangeAfter = document.getElementById('email-change-after');
  var changeEmailAfterBtn = document.getElementById('btn-change-email-after');

  if (emailInput && emailBtn && emailText && emailOtpSendUrl) {
    emailBtn.addEventListener('click', function () {
      var value = (emailInput.value || '').trim();
      if (!value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        emailText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Enter a valid email address";
        emailText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        emailInput.classList.add('is-invalid');
        return;
      }
      emailInput.classList.remove('is-invalid');
      emailBtn.disabled = true;
      emailBtn.textContent = 'Sending...';

      fetch(emailOtpSendUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf ? csrf.value : '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ email: value })
      })
        .then(function (res) { return res.json().then(function (data) { return { status: res.status, data: data }; }); })
        .then(function (resp) {
          var ok = resp.status >= 200 && resp.status < 300 && resp.data && resp.data.ok;
          if (ok) {
            emailText.innerHTML = "<i class='bx bx-check-circle me-1'></i>Verification code sent";
            emailText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-success';
            if (emailInputGroup) emailInputGroup.style.display = 'none';
            if (emailOtpBlock) emailOtpBlock.style.display = '';
            if (otpEmailDisplay) otpEmailDisplay.textContent = value;
            if (emailOtpCode) {
              emailOtpCode.value = '';
              emailOtpCode.focus();
            }
          } else {
            var msg = (resp.data && resp.data.message) ? resp.data.message : 'Failed to send code';
            emailText.innerHTML = "<i class='bx bx-x-circle me-1'></i>" + msg;
            emailText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
          }
        })
        .catch(function () {
          emailText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Network error. Try again.";
          emailText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        })
        .finally(function () {
          emailBtn.disabled = false;
          emailBtn.textContent = 'Verify';
        });
    });
  }

  if (changeEmailBtn && emailInputGroup && emailOtpBlock) {
    changeEmailBtn.addEventListener('click', function () {
      if (emailOtpBlock) emailOtpBlock.style.display = 'none';
      if (emailInputGroup) emailInputGroup.style.display = '';
      if (emailOtpCode) emailOtpCode.value = '';
      if (emailText) {
        emailText.textContent = '';
        emailText.className = 'form-text d-flex align-items-center gap-1 mt-1';
      }
      if (emailInput) {
        emailInput.readOnly = false;
        emailInput.focus();
      }
    });
  }

  if (emailOtpSubmit && emailInput && emailOtpCode && emailText && emailOtpVerifyUrl) {
    emailOtpSubmit.addEventListener('click', function () {
      var value = (emailInput.value || '').trim();
      var code = (emailOtpCode.value || '').trim();
      if (!code || code.length !== 6) {
        emailText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Enter the 6-digit code";
        emailText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        emailOtpCode.focus();
        return;
      }
      emailOtpSubmit.disabled = true;
      emailOtpSubmit.textContent = 'Verifying...';

      fetch(emailOtpVerifyUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf ? csrf.value : '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ email: value, otp: code })
      })
        .then(function (res) { return res.json().then(function (data) { return { status: res.status, data: data }; }); })
        .then(function (resp) {
          var ok = resp.status >= 200 && resp.status < 300 && resp.data && resp.data.ok;
          if (ok) {
            emailText.innerHTML = "<i class='bx bx-check-circle me-1'></i>Email verified successfully!";
            emailText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-success';
            // Hide OTP UI and show verified badge
            if (emailOtpBlock) emailOtpBlock.style.display = 'none';
            if (emailInputGroup) emailInputGroup.style.display = '';
            if (emailBtn) emailBtn.style.display = 'none';
            if (emailOtpCode) emailOtpCode.value = '';
            // Lock email field
            emailInput.readOnly = true;
            // Show verified badge
            var badge = document.createElement('span');
            badge.id = 'email-verified-badge';
            badge.className = 'input-group-text bg-success text-white';
            badge.innerHTML = "<i class='bx bx-check-circle me-1'></i>Verified";
            emailInputGroup.appendChild(badge);
            // Show change link
            if (emailChangeAfter) emailChangeAfter.classList.remove('d-none');
            
            // Reload page after 2 seconds to refresh state
            setTimeout(function() {
              window.location.reload();
            }, 2000);
          } else {
            var msg = (resp.data && resp.data.message) ? resp.data.message : 'Verification failed';
            emailText.innerHTML = "<i class='bx bx-x-circle me-1'></i>" + msg;
            emailText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
          }
        })
        .catch(function () {
          emailText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Network error. Try again.";
          emailText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        })
        .finally(function () {
          emailOtpSubmit.disabled = false;
          emailOtpSubmit.textContent = 'Submit OTP';
        });
    });
  }

  if (changeEmailAfterBtn && emailInputGroup) {
    changeEmailAfterBtn.addEventListener('click', function () {
      var badge = document.getElementById('email-verified-badge');
      if (badge) badge.remove();
      if (emailBtn) emailBtn.style.display = '';
      if (emailChangeAfter) emailChangeAfter.classList.add('d-none');
      if (emailInput) {
        emailInput.readOnly = false;
        emailInput.focus();
      }
      if (emailText) {
        emailText.textContent = '';
        emailText.className = 'form-text d-flex align-items-center gap-1 mt-1';
      }
    });
  }
})();
