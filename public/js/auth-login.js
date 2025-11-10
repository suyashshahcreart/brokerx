(function () {
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

  var csrf = document.querySelector('input[name="_token"]');
  var otpSendUrl = formEl ? formEl.getAttribute('data-otp-send') : null;
  var otpVerifyUrl = formEl ? formEl.getAttribute('data-otp-verify') : null; // not used now (server verifies on submit)
  var emailOtpSendUrl = formEl ? formEl.getAttribute('data-email-otp-send') : null;
  var emailOtpVerifyUrl = formEl ? formEl.getAttribute('data-email-otp-verify') : null; // not used now

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
})();


