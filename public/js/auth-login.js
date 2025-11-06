(function () {
  var formEl = document.querySelector('form.authentication-form');
  var identifier = document.getElementById('login-identifier');
  var passwordBlock = document.getElementById('password-block');
  var passwordInput = document.getElementById('login-password');
  var otpBlock = document.getElementById('otp-login-block');
  var toggleOtpBtn = document.getElementById('login-toggle-otp');
  var togglePasswordBtn = document.getElementById('login-toggle-password');
  var sendOtpBtn = document.getElementById('login-send-otp');
  var submitOtpBtn = document.getElementById('login-submit-otp');
  var otpCodeInput = document.getElementById('login-otp-code');
  var otpText = document.getElementById('login-otp-text');
  var otpVerifiedInput = document.getElementById('login-otp-verified');
  var submitBtn = document.getElementById('btn-submit-login');

  var csrf = document.querySelector('input[name="_token"]');
  var otpSendUrl = formEl ? formEl.getAttribute('data-otp-send') : null;
  var otpVerifyUrl = formEl ? formEl.getAttribute('data-otp-verify') : null;
  var emailOtpSendUrl = formEl ? formEl.getAttribute('data-email-otp-send') : null;
  var emailOtpVerifyUrl = formEl ? formEl.getAttribute('data-email-otp-verify') : null;

  function showOtpMode() {
    if (passwordBlock) passwordBlock.style.display = 'none';
    if (otpBlock) otpBlock.style.display = '';
    if (otpCodeInput) otpCodeInput.value = '';
    if (otpText) { otpText.textContent = ''; otpText.className = 'form-text d-flex align-items-center gap-1 mt-1'; }
    if (otpVerifiedInput) otpVerifiedInput.value = '0';
  }

  function showPasswordMode() {
    if (otpBlock) otpBlock.style.display = 'none';
    if (passwordBlock) passwordBlock.style.display = '';
    if (otpVerifiedInput) otpVerifiedInput.value = '0';
  }

  if (toggleOtpBtn) toggleOtpBtn.addEventListener('click', showOtpMode);
  if (togglePasswordBtn) togglePasswordBtn.addEventListener('click', showPasswordMode);

  function isMobile(value) {
    return /^\+?\d{8,20}$/.test(value || '');
  }
  function isEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value || '');
  }

  if (sendOtpBtn && identifier) {
    sendOtpBtn.addEventListener('click', function () {
      var idv = (identifier.value || '').trim();
      var useMobile = isMobile(idv);
      var useEmail = isEmail(idv);
      if (!useMobile && !useEmail) {
        otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Enter a valid email or mobile to receive OTP";
        otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        return;
      }
      sendOtpBtn.disabled = true;
      sendOtpBtn.textContent = 'Sending...';
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
            otpText.innerHTML = "<i class='bx bx-check-circle me-1'></i>OTP sent";
            otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-success';
            if (otpCodeInput) { otpCodeInput.value = ''; otpCodeInput.focus(); }
          } else {
            var msg = (resp.data && resp.data.message) ? resp.data.message : 'Failed to send OTP';
            otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>" + msg;
            otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
          }
        })
        .catch(function () {
          otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Network error. Try again.";
          otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        })
        .finally(function () {
          sendOtpBtn.disabled = false;
          sendOtpBtn.textContent = 'Send OTP';
        });
    });
  }

  if (submitOtpBtn && identifier) {
    submitOtpBtn.addEventListener('click', function () {
      var idv = (identifier.value || '').trim();
      var code = (otpCodeInput.value || '').trim();
      var useMobile = isMobile(idv);
      var useEmail = isEmail(idv);
      if (!useMobile && !useEmail) {
        otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Enter a valid email or mobile";
        otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        return;
      }
      if (!code || code.length !== 6) {
        otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Enter the 6-digit code";
        otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        otpCodeInput.focus();
        return;
      }
      submitOtpBtn.disabled = true;
      submitOtpBtn.textContent = 'Verifying...';
      var url = useMobile ? otpVerifyUrl : emailOtpVerifyUrl;
      var payload = useMobile ? { mobile: idv, code: code } : { email: idv, code: code };
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.value : '' },
        body: JSON.stringify(payload)
      })
        .then(function (res) { return res.json().then(function (data) { return { status: res.status, data: data }; }); })
        .then(function (resp) {
          var ok = resp.status >= 200 && resp.status < 300 && resp.data && resp.data.ok;
          if (ok) {
            otpText.innerHTML = "<i class='bx bx-check-circle me-1'></i>OTP verified. You can sign in now.";
            otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-success';
            if (otpVerifiedInput) otpVerifiedInput.value = '1';
            if (submitBtn) submitBtn.focus();
          } else {
            var msg = (resp.data && resp.data.message) ? resp.data.message : 'Verification failed';
            otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>" + msg;
            otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
          }
        })
        .catch(function () {
          otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Network error. Try again.";
          otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        })
        .finally(function () {
          submitOtpBtn.disabled = false;
          submitOtpBtn.textContent = 'Submit OTP';
        });
    });
  }

  // Optional: prevent submit in OTP mode unless verified
  if (formEl) {
    formEl.addEventListener('submit', function (e) {
      var otpMode = otpBlock && otpBlock.style.display !== 'none';
      if (otpMode && otpVerifiedInput && otpVerifiedInput.value !== '1') {
        e.preventDefault();
        if (otpText) {
          otpText.innerHTML = "<i class='bx bx-x-circle me-1'></i>Please verify OTP before signing in";
          otpText.className = 'form-text d-flex align-items-center gap-1 mt-1 text-danger';
        }
        if (otpCodeInput) otpCodeInput.focus();
      }
    });
  }
})();


