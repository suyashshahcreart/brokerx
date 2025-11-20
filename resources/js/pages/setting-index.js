// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('settingsForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('updateSettingsBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';

        const csrf = form.getAttribute('data-csrf');
        const avaliableDaysInput = document.getElementById('avaliable_days');
        const value = avaliableDaysInput ? avaliableDaysInput.value : '';
        const data = {
            name: 'avaliable_days',
            value: value
        };

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
            credentials: 'same-origin',
        })
        .then(response => response.json())
        .then(json => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-save-line me-1"></i> Update Settings';
            if (json.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Settings updated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert('Settings updated successfully!');
                }
            } else {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: json.message || 'Failed to update settings.'
                    });
                } else {
                    alert(json.message || 'Failed to update settings.');
                }
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-save-line me-1"></i> Update Settings';
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update settings.'
                });
            } else {
                alert('Failed to update settings.');
            }
        });
    });
});