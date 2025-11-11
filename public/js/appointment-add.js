// Form validation for appointment
    (function () {
        'use strict';

        // Fetch all the forms we want to apply custom Bootstrap validation to
        var forms = document.querySelectorAll('.needs-validation');

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            }, false);
        });

        // Validate end time is after start time
        const startTime = document.getElementById('start-time');
        const endTime = document.getElementById('end-time');

        if (startTime && endTime) {
            endTime.addEventListener('change', function () {
                if (startTime.value && endTime.value) {
                    if (endTime.value <= startTime.value) {
                        endTime.setCustomValidity('End time must be after start time');
                    } else {
                        endTime.setCustomValidity('');
                    }
                }
            });
        }
    })();
