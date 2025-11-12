(function(){
    // Scheduler login OTP helper
    const form = document.getElementById('scheduler-otp-form');
    if (!form) return;

    const sendBtn = document.getElementById('btn-send-otp');
    const resendBtn = document.getElementById('btn-resend-otp');
    const mobileInput = document.getElementById('login-mobile');
    const otpBlock = document.getElementById('otp-block');
    const otpCodeInput = document.getElementById('login-otp-code');
    const otpText = document.getElementById('login-otp-text');
    const submitBtn = document.getElementById('btn-submit-login');

    // Get CSRF token from meta tag or form
    function getCsrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        const input = form.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    // Get send OTP URL from form data attribute or default
    const sendUrl = form.dataset.sendUrl || '/schedulers/otp/send';

    async function sendOtp(){
        const identifier = mobileInput.value.trim();
        
        if(!identifier){
            alert('Please enter mobile number or email');
            return;
        }

        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';

        try {
            const res = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrf(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ identifier })
            });

            const json = await res.json();

            if(res.ok && json.ok){
                otpBlock.style.display = 'block';
                otpText.classList.remove('text-danger');
                otpText.classList.add('text-success');
                otpText.textContent = 'Code sent successfully. It will expire in ' + (json.ttl || 300) + ' seconds.';
                resendBtn.style.display = 'inline-block';
                sendBtn.style.display = 'none';
                
                // Focus on OTP input
                if(otpCodeInput) otpCodeInput.focus();
            } else {
                otpText.classList.remove('text-success');
                otpText.classList.add('text-danger');
                otpText.textContent = json.message || 'Unable to send OTP. Please try again.';
                alert(json.message || 'Unable to send OTP');
            }
        } catch(err) {
            console.error('Error sending OTP:', err);
            otpText.classList.remove('text-success');
            otpText.classList.add('text-danger');
            otpText.textContent = 'Network error. Please check your connection.';
            alert('Error sending OTP. Please try again.');
        } finally {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send OTP';
        }
    }

    // Enable/disable submit button based on OTP input
    function validateOtpInput() {
        if(otpCodeInput) {
            const code = otpCodeInput.value.trim();
            const isValid = /^[0-9]{6}$/.test(code);
            if(submitBtn) {
                submitBtn.disabled = !isValid;
            }
        }
    }

    // Attach event listeners
    if(sendBtn) {
        sendBtn.addEventListener('click', sendOtp);
    }

    if(resendBtn) {
        resendBtn.addEventListener('click', function(){
            // Reset UI for resend
            sendBtn.style.display = 'inline-block';
            resendBtn.style.display = 'none';
            if(otpCodeInput) otpCodeInput.value = '';
            sendOtp();
        });
    }

    if(otpCodeInput) {
        otpCodeInput.addEventListener('input', validateOtpInput);
        otpCodeInput.addEventListener('keyup', function(e){
            if(e.key === 'Enter' && otpCodeInput.value.trim().length === 6) {
                form.submit();
            }
        });
    }

    // Form validation before submit
    form.addEventListener('submit', function(e){
        const identifier = mobileInput.value.trim();
        const code = otpCodeInput ? otpCodeInput.value.trim() : '';

        if(!identifier) {
            e.preventDefault();
            alert('Please enter mobile number or email');
            mobileInput.focus();
            return false;
        }

        if(!code || code.length !== 6) {
            e.preventDefault();
            alert('Please enter the 6-digit OTP code');
            if(otpCodeInput) otpCodeInput.focus();
            return false;
        }
    });

    // Initialize: disable submit button until OTP is entered
    if(submitBtn) {
        submitBtn.disabled = true;
    }

})();
