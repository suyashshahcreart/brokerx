// Wait for DOM and ensure script runs only once
if (document.getElementById('tour-dropzone') && !document.getElementById('tour-dropzone').dropzone) {
    
    // Check if Dropzone is available
    if (typeof Dropzone === 'undefined') {
        console.error('Dropzone library not loaded');
    } else {
        Dropzone.autoDiscover = false;

        const myDropzone = new Dropzone("#tour-dropzone", {
            url: "#", // Dummy URL since we'll submit via form
            paramName: "files",
            maxFilesize: 500, // MB (increased for zip files)
            maxFiles: 10, // Maximum 10 files
            acceptedFiles: ".zip,image/*,.pdf,.doc,.docx",
            addRemoveLinks: true,
            clickable: true,
            autoProcessQueue: false, // Don't upload automatically
            uploadMultiple: true,
            dictDefaultMessage: "Drop tour ZIP file here or click to select",
            dictFileTooBig: "File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.",
            dictInvalidFileType: "You can't upload files of this type. Upload a ZIP file containing tour assets, or images/PDF/DOC.",
            dictMaxFilesExceeded: "You can't upload more than {{maxFiles}} files.",
            dictRemoveFile: "Remove file",
            init: function () {
                const dropzone = this;
                
                // File validation on add
                this.on("addedfile", function(file) {
                    console.log('File added:', file.name);
                    
                    // Validate file size
                    if (file.size > dropzone.options.maxFilesize * 1024 * 1024) {
                        dropzone.removeFile(file);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Too Large',
                                text: `${file.name} is too large. Maximum file size is ${dropzone.options.maxFilesize}MB.`
                            });
                        } else {
                            alert(`File ${file.name} is too large. Maximum size is ${dropzone.options.maxFilesize}MB.`);
                        }
                        return;
                    }
                    
                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
                                       'application/pdf', 'application/msword', 
                                       'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                       'application/zip', 'application/x-zip-compressed', 'application/x-zip', 'application/octet-stream'];
                    
                    const isZipFile = file.name.toLowerCase().endsWith('.zip');
                    
                    if (!validTypes.includes(file.type) && !isZipFile) {
                        dropzone.removeFile(file);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid File Type',
                                text: `${file.name} is not an allowed file type. Only images, PDF, DOC, DOCX, and ZIP files are allowed.`
                            });
                        } else {
                            alert(`File ${file.name} is not allowed. Only images, PDF, DOC, DOCX, and ZIP files are accepted.`);
                        }
                        return;
                    }
                    
                    // Show file count
                    updateFileCount();
                });

                this.on("removedfile", function (file) {
                    console.log('File removed:', file.name);
                    updateFileCount();
                });
                
                this.on("maxfilesexceeded", function(file) {
                    this.removeFile(file);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Too Many Files',
                            text: `Maximum ${dropzone.options.maxFiles} files allowed.`
                        });
                    } else {
                        alert(`You can only upload a maximum of ${dropzone.options.maxFiles} files.`);
                    }
                });
                
                // Update file count display
                function updateFileCount() {
                    const fileCount = dropzone.files.length;
                    const countDisplay = document.getElementById('file-count-display');
                    if (countDisplay) {
                        countDisplay.textContent = `${fileCount} file(s) selected`;
                        countDisplay.style.display = fileCount > 0 ? 'block' : 'none';
                    }
                }
                
                // Handle form submission
                const form = document.getElementById('tour-edit-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Validate form has files or is updating existing tour
                        const files = dropzone.getAcceptedFiles();
                        const hasExistingFiles = document.querySelector('.list-group-item') !== null;
                        
                        if (files.length === 0 && !hasExistingFiles) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'No Files Selected',
                                    text: 'Please select at least one file to upload.',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                alert('Please select at least one file to upload.');
                            }
                            return;
                        }
                        
                        // Validate total file size
                        let totalSize = 0;
                        files.forEach(function(file) {
                            totalSize += file.size;
                        });
                        
                        const maxTotalSize = 200 * 1024 * 1024; // 200MB total
                        if (totalSize > maxTotalSize) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Total Size Too Large',
                                    text: `Total file size exceeds 200MB. Please remove some files.`
                                });
                            } else {
                                alert('Total file size exceeds 200MB. Please remove some files.');
                            }
                            return;
                        }
                        
                        // Create FormData from the form
                        const formData = new FormData(form);
                        
                        // Add all dropzone files to FormData
                        files.forEach(function(file) {
                            formData.append('files[]', file);
                        });
                        
                        // Show loading overlay
                        const loadingOverlay = document.getElementById('tour-loading-overlay');
                        const progressBar = document.getElementById('upload-progress-bar');
                        const uploadStatus = document.getElementById('upload-status');
                        
                        if (loadingOverlay) {
                            loadingOverlay.style.display = 'flex';
                            progressBar.style.width = '10%';
                            uploadStatus.textContent = 'Uploading files...';
                        }
                        
                        // Show loading state on button
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalBtnText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="ri-loader-4-line me-1"></i> Updating...';
                        
                        // Simulate progress
                        let progress = 10;
                        const progressInterval = setInterval(() => {
                            if (progress < 90) {
                                progress += 10;
                                if (progressBar) progressBar.style.width = progress + '%';
                                
                                if (progress === 30) uploadStatus.textContent = 'Processing files...';
                                if (progress === 60) uploadStatus.textContent = 'Extracting tour data...';
                                if (progress === 80) uploadStatus.textContent = 'Almost done...';
                            }
                        }, 500);
                        
                        // Submit form via AJAX
                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            clearInterval(progressInterval);
                            
                            if (data.success) {
                                // Complete progress
                                if (progressBar) progressBar.style.width = '100%';
                                if (uploadStatus) uploadStatus.textContent = 'Upload complete!';
                                
                                setTimeout(() => {
                                    if (loadingOverlay) loadingOverlay.style.display = 'none';
                                    
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: data.message || 'Tour updated successfully!',
                                            timer: 2000
                                        }).then(() => {
                                            window.location.href = data.redirect || form.action.replace(/\/\d+$/, '/' + data.booking_id);
                                        });
                                    } else {
                                        alert(data.message || 'Tour updated successfully!');
                                        window.location.reload();
                                    }
                                }, 500);
                            } else {
                                throw new Error(data.message || 'Update failed');
                            }
                        })
                        .catch(error => {
                            console.error('Submit error:', error);
                            clearInterval(progressInterval);
                            
                            // Hide loading overlay
                            if (loadingOverlay) loadingOverlay.style.display = 'none';
                            
                            // Re-enable button
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnText;
                            
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message || 'Failed to update tour'
                                });
                            } else {
                                alert('Failed to update tour: ' + error.message);
                            }
                        });
                    });
                }
            }
        });
    }
}
