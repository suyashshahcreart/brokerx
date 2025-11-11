(function(){
    // Scheduler registration OTP helper
    const form = document.querySelector('.authentication-form');
    if (!form) return;

    const sendUrl = form.dataset.otpSend;
    const verifyUrl = form.dataset.otpVerify;
    const csrf = form.querySelector('input[name="_token"]').value;

    const btnVerify = document.getElementById('btn-verify-mobile');
    const mobileInput = document.getElementById('mobile');
    const emailInput = document.getElementById('example-email');
    const otpBlock = document.getElementById('otp-block');
    const otpCodeInput = document.getElementById('otp-code');
    const btnSubmitOtp = document.getElementById('btn-submit-otp');
    const otpDisplay = document.getElementById('otp-mobile-display');
    const verifiedBadge = document.getElementById('mobile-verified-badge');
    const mobileVerifyText = document.getElementById('mobile-verify-text');
    const btnChangeMobile = document.getElementById('btn-change-mobile');
    const btnSubmitRegister = document.getElementById('btn-submit-register');
    const checkbox = document.getElementById('checkbox-signin');

    let verified = false;
    let verifiedKind = null; // 'mobile' or 'email'
    let verifiedValue = null;

    function maskValue(v){
        if (!v) return '';
        if (v.indexOf('@') !== -1){
            const parts = v.split('@');
            const name = parts[0];
            const domain = parts[1];
            return name.slice(0,1) + '***@' + domain;
        }
        // mobile mask
        return v.slice(0,3) + '****' + v.slice(-2);
    }

    function enableRegisterIfReady(){
        const firstname = form.querySelector('input[name="firstname"]').value.trim();
        const lastname = form.querySelector('input[name="lastname"]').value.trim();
        const mobile = mobileInput.value.trim();
        const email = emailInput.value.trim();
        const terms = checkbox && checkbox.checked;

        // require names, at least one contact filled, verified, and terms
        const contactFilled = mobile || email;
        const ok = firstname && lastname && contactFilled && verified && terms;
        btnSubmitRegister.disabled = !ok;
    }

    // watch inputs and checkbox
    ['input','change'].forEach(ev => {
        form.addEventListener(ev, enableRegisterIfReady);
    });

    if (btnVerify){
        btnVerify.addEventListener('click', async function(){
            mobileVerifyText.textContent = '';
            const mobile = mobileInput.value.trim();
            const email = emailInput.value.trim();
            let identifier = null;
            if (email && !mobile){
                identifier = email;
            } else if (mobile && mobile.indexOf('@')!==-1){
                identifier = mobile; // unlikely, but handle
            } else {
                identifier = mobile || email;
            }

            if (!identifier){
                mobileVerifyText.innerHTML = '<span class="text-danger">Please enter mobile number or email to verify.</span>';
                return;
            }

            btnVerify.disabled = true;
            btnVerify.textContent = 'Sending...';

            try{
                const res = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ identifier })
                });
                const data = await res.json();
                if (!res.ok){
                    throw new Error(data.message || 'Failed to send OTP');
                }
                // show otp block
                otpBlock.style.display = '';
                otpDisplay.textContent = maskValue(identifier);
                mobileVerifyText.innerHTML = '<span class="text-success">OTP sent. Enter the 6-digit code.</span>';
                verified = false;
                verifiedKind = identifier.indexOf('@') !== -1 ? 'email' : 'mobile';
                verifiedValue = identifier;
            }catch(err){
                mobileVerifyText.innerHTML = '<span class="text-danger">'+ (err.message || 'Error sending OTP') +'</span>';
                console.error(err);
            }finally{
                btnVerify.disabled = false;
                btnVerify.textContent = 'Verify';
            }
        });
    }

    if (btnSubmitOtp){
        btnSubmitOtp.addEventListener('click', async function(){
            const code = otpCodeInput.value.trim();
            if (!/^[0-9]{6}$/.test(code)){
                mobileVerifyText.innerHTML = '<span class="text-danger">Enter a valid 6-digit code.</span>';
                return;
            }
            btnSubmitOtp.disabled = true;
            btnSubmitOtp.textContent = 'Verifying...';
            try{
                const payload = { code, for_registration: true };
                if (verifiedValue){ payload.identifier = verifiedValue; }
                else {
                    const mobile = mobileInput.value.trim();
                    const email = emailInput.value.trim();
                    if (email && !mobile) payload.identifier = email;
                    else payload.identifier = mobile || email;
                }
                const res = await fetch(verifyUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok || !data.ok){
                    throw new Error(data.message || 'Invalid code');
                }
                // success
                verified = true;
                // show badge
                verifiedBadge.classList.remove('d-none');
                mobileVerifyText.innerHTML = '<span class="text-success">Verified</span>';
                otpBlock.style.display = 'none';
                // make mobile read-only but keep email editable per requirement
                if (mobileInput) mobileInput.readOnly = true;
                if (emailInput) emailInput.readOnly = false;
                // hide the verify button next to mobile after successful verification
                if (btnVerify) btnVerify.style.display = 'none';

                // attach hidden verification markers to the form so server can validate
                const kind = (verifiedKind || (verifiedValue && verifiedValue.indexOf('@') !== -1 ? 'email' : 'mobile'));
                // helper to ensure hidden input exists
                function setHidden(name, value){
                    let el = form.querySelector('input[name="' + name + '"]');
                    if (!el){
                        el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = name;
                        form.appendChild(el);
                    }
                    el.value = value;
                }
                setHidden('verified', '1');
                setHidden('verified_kind', kind);
                setHidden('verified_value', verifiedValue || (kind === 'email' ? emailInput.value.trim() : mobileInput.value.trim()));

                // make sure submit button is visible and enable it if other inputs ok
                if (btnSubmitRegister){
                    btnSubmitRegister.style.display = '';
                }
                enableRegisterIfReady();
            }catch(err){
                mobileVerifyText.innerHTML = '<span class="text-danger">'+ (err.message || 'Verification failed') +'</span>';
                console.error(err);
            }finally{
                btnSubmitOtp.disabled = false;
                btnSubmitOtp.textContent = 'Submit OTP';
            }
        });
    }

    if (btnChangeMobile){
        btnChangeMobile.addEventListener('click', function(){
            otpBlock.style.display = 'none';
            mobileInput.readOnly = false;
            emailInput.readOnly = false;
            otpCodeInput.value = '';
            verified = false;
            verifiedBadge.classList.add('d-none');
            // remove hidden verification markers
            ['verified','verified_kind','verified_value'].forEach(name => {
                const el = form.querySelector('input[name="'+name+'"]');
                if (el) el.remove();
            });
            // re-show verify button when changing mobile/email
            if (btnVerify) btnVerify.style.display = '';
            enableRegisterIfReady();
        });
    }

    // ensure register button can be clicked when form is valid
    form.addEventListener('submit', function(e){
        if (!verified){
            e.preventDefault();
            mobileVerifyText.innerHTML = '<span class="text-danger">Please verify your contact before registering.</span>';
            return false;
        }
    });

})();
