(function () {
  // Toggle show/hide password
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.password-toggle');
    if (!btn) return;
    var targetId = btn.getAttribute('data-target');
    var input = document.getElementById(targetId);
    if (!input) return;
    var isHidden = input.getAttribute('type') === 'password';
    input.setAttribute('type', isHidden ? 'text' : 'password');
    var icon = btn.querySelector('i');
    if (icon) {
      icon.classList.toggle('bx-hide', !isHidden);
      icon.classList.toggle('bx-show', isHidden);
    }
    btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
  });

  var passwordInput = document.getElementById('example-password');
  var bar = document.getElementById('password-strength-bar');
  var reqLength = document.getElementById('req-length');
  var reqUpper = document.getElementById('req-upper');
  var reqLower = document.getElementById('req-lower');
  var reqNumber = document.getElementById('req-number');
  var reqSpecial = document.getElementById('req-special');
  var reqLengthItem = document.getElementById('req-length-item');
  var reqUpperItem = document.getElementById('req-upper-item');
  var reqLowerItem = document.getElementById('req-lower-item');
  var reqNumberItem = document.getElementById('req-number-item');
  var reqSpecialItem = document.getElementById('req-special-item');
  var reqLengthIcon = document.getElementById('req-length-icon');
  var reqUpperIcon = document.getElementById('req-upper-icon');
  var reqLowerIcon = document.getElementById('req-lower-icon');
  var reqNumberIcon = document.getElementById('req-number-icon');
  var reqSpecialIcon = document.getElementById('req-special-icon');
  var confirmInput = document.getElementById('confirm-password');
  var confirmText = document.getElementById('confirm-password-text');
  var formEl = document.querySelector('form.authentication-form');
  var submitBtn = document.getElementById('btn-submit-register');
  var firstNameInput = document.getElementById('first-name');
  var lastNameInput = document.getElementById('last-name');
  var emailInput = document.getElementById('example-email');

  if (passwordInput && bar) {
    function scorePassword(pw) {
      var score = 0;
      if (!pw) return 0;
      if (pw.length >= 8) score += 20;
      if (pw.length >= 12) score += 15;
      if (/[a-z]/.test(pw)) score += 15;
      if (/[A-Z]/.test(pw)) score += 15;
      if (/[0-9]/.test(pw)) score += 15;
      if (/[^A-Za-z0-9]/.test(pw)) score += 20;
      return Math.min(score, 100);
    }

    function renderBar(pct) {
      var cls = 'bg-danger';
      if (pct >= 70) cls = 'bg-success';
      else if (pct >= 50) cls = 'bg-info';
      else if (pct >= 30) cls = 'bg-warning';
      bar.className = 'progress-bar ' + cls;
      bar.style.width = pct + '%';
      bar.setAttribute('aria-valuenow', String(pct));
    }

    function updateReq(itemEl, textEl, iconEl, ok) {
      if (itemEl) itemEl.className = 'd-flex align-items-center ' + (ok ? 'text-success' : 'text-danger');
      if (textEl) textEl.className = ok ? 'text-success' : 'text-danger';
      if (iconEl) {
        iconEl.classList.toggle('bx-check-circle', ok);
        iconEl.classList.toggle('bx-x-circle', !ok);
      }
    }

    function updateRequirements(pw) {
      updateReq(reqLengthItem, reqLength, reqLengthIcon, pw.length >= 8);
      updateReq(reqUpperItem, reqUpper, reqUpperIcon, /[A-Z]/.test(pw));
      updateReq(reqLowerItem, reqLower, reqLowerIcon, /[a-z]/.test(pw));
      updateReq(reqNumberItem, reqNumber, reqNumberIcon, /[0-9]/.test(pw));
      updateReq(reqSpecialItem, reqSpecial, reqSpecialIcon, /[^A-Za-z0-9]/.test(pw));
    }

    function updateConfirmMatch() {
      if (!confirmInput || !confirmText) return;
      var pw = passwordInput.value || '';
      var cpw = confirmInput.value || '';
      if (!cpw) {
        confirmText.textContent = '';
        confirmText.className = 'form-text d-flex align-items-center gap-1 mt-1';
        confirmInput.classList.remove('is-invalid');
        return;
      }
      var match = pw === cpw;
      confirmText.innerHTML = (match
        ? "<i class='bx bx-check-circle me-1'></i>Passwords match"
        : "<i class='bx bx-x-circle me-1'></i>Passwords do not match");
      confirmText.className = 'form-text d-flex align-items-center gap-1 mt-1 ' + (match ? 'text-success' : 'text-danger');
      if (!match) confirmInput.classList.add('is-invalid');
      else confirmInput.classList.remove('is-invalid');
    }

    passwordInput.addEventListener('input', function () {
      var pw = passwordInput.value || '';
      renderBar(scorePassword(pw));
      updateRequirements(pw);
      updateConfirmMatch();
      updateSubmitState();
    });

    if (confirmInput) {
      confirmInput.addEventListener('input', function(){ updateConfirmMatch(); updateSubmitState(); });
      confirmInput.addEventListener('blur', function(){ updateConfirmMatch(); updateSubmitState(); });
    }

    // Initialize
    var initPw = passwordInput.value || '';
    renderBar(scorePassword(initPw));
    updateRequirements(initPw);
    updateConfirmMatch();
  }

  function isMobileVerified() {
    var badge = document.getElementById('mobile-verified-badge');
    return !!(badge && !badge.classList.contains('d-none'));
  }

  function fieldsFilled() {
    var fn = (firstNameInput && firstNameInput.value.trim().length > 0);
    var ln = (lastNameInput && lastNameInput.value.trim().length > 0);
    var mob = (mobileInput && mobileInput.value.trim().length > 0);
    var pw = (passwordInput && passwordInput.value.trim().length >= 8);
    var cpw = (confirmInput && confirmInput.value.trim().length >= 1);
    var match = (passwordInput && confirmInput && passwordInput.value === confirmInput.value);
    return fn && ln && mob && pw && cpw && match;
  }

  function updateSubmitState() {
    if (!submitBtn) return;
    var enable = fieldsFilled() && isMobileVerified();
    submitBtn.disabled = !enable;
  }

  if (formEl && passwordInput && confirmInput) {
    formEl.addEventListener('submit', function (e) {
      var pw = passwordInput.value || '';
      var cpw = confirmInput.value || '';
      if (pw !== cpw) {
        e.preventDefault();
        var ev = new Event('input');
        confirmInput.dispatchEvent(ev);
        confirmInput.focus();
      }
    });
  }

  // Mobile verify with backend OTP flow
  var mobileInput = document.getElementById('mobile');
  var mobileInputGroup = document.getElementById('mobile-input-group');
  var mobileBtn = document.getElementById('btn-verify-mobile');
  var mobileText = document.getElementById('mobile-verify-text');
  var otpBlock = document.getElementById('otp-block');
  var otpCode = document.getElementById('otp-code');
  var otpSubmit = document.getElementById('btn-submit-otp');
  var otpMobileDisplay = document.getElementById('otp-mobile-display');
  var changeMobileBtn = document.getElementById('btn-change-mobile');
  var csrf = document.querySelector('input[name="_token"]');
  var otpSendUrl = formEl ? formEl.getAttribute('data-otp-send') : null;
  var otpVerifyUrl = formEl ? formEl.getAttribute('data-otp-verify') : null;
  var mobileVerifiedBadge = document.getElementById('mobile-verified-badge');
  var mobileChangeAfter = document.getElementById('mobile-change-after');
  var changeMobileAfterBtn = document.getElementById('btn-change-mobile-after');

  if (mobileInput && mobileBtn && mobileText && otpSendUrl) {
    mobileBtn.addEventListener('click', function () {
      var value = (mobileInput.value || '').trim();
      if (!value || value.length < 8) {
        mobileText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Enter a valid mobile number";
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
          'X-CSRF-TOKEN': csrf ? csrf.value : ''
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
            updateSubmitState();
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

  // Change mobile number: go back to edit state
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
      if (mobileVerifiedBadge) mobileVerifiedBadge.classList.add('d-none');
      if (mobileBtn) mobileBtn.classList.remove('d-none');
      if (mobileChangeAfter) mobileChangeAfter.classList.add('d-none');
      if (otpSubmit) otpSubmit.disabled = false;
      updateSubmitState();
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
          'X-CSRF-TOKEN': csrf ? csrf.value : ''
        },
        body: JSON.stringify({ mobile: value, code: code })
      })
        .then(function (res) { return res.json().then(function (data) { return { status: res.status, data: data }; }); })
        .then(function (resp) {
          var ok = resp.status >= 200 && resp.status < 300 && resp.data && resp.data.ok;
          if (ok) {
            mobileText.innerHTML = "";
            mobileText.className = 'd-none';
            // Hide OTP UI and show mobile with verified badge
            if (otpBlock) otpBlock.style.display = 'none';
            if (mobileInputGroup) mobileInputGroup.style.display = '';
            if (mobileVerifiedBadge) mobileVerifiedBadge.classList.remove('d-none');
            if (mobileBtn) mobileBtn.classList.add('d-none');
            if (otpCode) otpCode.value = '';
            // Lock mobile field to prevent accidental edits
            mobileInput.readOnly = true;
            // otpSubmit.disabled = true;
            if (mobileChangeAfter) mobileChangeAfter.classList.remove('d-none');
            updateSubmitState();
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

  // Change mobile number after verified (below mobile field)
  if (changeMobileAfterBtn && mobileInputGroup) {
    changeMobileAfterBtn.addEventListener('click', function () {
      if (mobileVerifiedBadge) mobileVerifiedBadge.classList.add('d-none');
      if (mobileBtn) mobileBtn.classList.remove('d-none');
      if (mobileChangeAfter) mobileChangeAfter.classList.add('d-none');
      if (mobileInput) {
        mobileInput.readOnly = false;
        mobileInput.focus();
      }
      updateSubmitState();
    });
  }
})();


