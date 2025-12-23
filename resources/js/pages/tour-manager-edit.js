// Function to update upload paths based on slug and location
function updateUploadPaths() {
    const slugInput = document.getElementById('tour_slug');
    const locationSelect = document.getElementById('tour_location');
    const ftpFullUrlText = document.getElementById('ftp-full-url-text');
    
    if (!slugInput || !locationSelect || !ftpFullUrlText) {
        return; // Elements not found, skip silently
    }
    
    const slug = slugInput.value.trim();
    const location = locationSelect.value;
    
    // Update FTP Full URL based on location and slug
    if (location === 'creart_qr') {
        if (slug) {
            ftpFullUrlText.textContent = `http://creart.in/qr/${slug}/index.php`;
        } else {
            ftpFullUrlText.textContent = 'N/A';
        }
    } else if (location === 'tours' && slug) {
        ftpFullUrlText.textContent = `https://tour.proppik.in/${slug}/index.php`;
    } else if (location && slug) {
        ftpFullUrlText.textContent = `https://${location}.proppik.com/${slug}/index.php`;
    } else {
        ftpFullUrlText.textContent = 'N/A';
    }
}

// Setup dynamic path updates - ensure it runs after DOM is ready
(function setupPathUpdates() {
    function attachEventListeners() {
        const slugInput = document.getElementById('tour_slug');
        const locationSelect = document.getElementById('tour_location');
        
        if (!slugInput || !locationSelect) {
            // Elements not ready yet, try again
            setTimeout(attachEventListeners, 100);
            return;
        }
        
        // Attach event listeners to slug input
        if (slugInput) {
            // Remove any existing listeners by using a wrapper function
            slugInput.oninput = updateUploadPaths;
            slugInput.onkeyup = updateUploadPaths;
            slugInput.onchange = updateUploadPaths;
            slugInput.onpaste = function() {
                setTimeout(updateUploadPaths, 10);
            };
        }
        
        // Attach event listeners to location select
        if (locationSelect) {
            locationSelect.onchange = updateUploadPaths;
            locationSelect.oninput = updateUploadPaths;
        }
        
        // Initial update
        updateUploadPaths();
    }
    
    // Try to attach listeners when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachEventListeners);
    } else {
        // DOM already loaded, try immediately
        attachEventListeners();
    }
    
    // Also try after a delay to catch late-loading elements
    setTimeout(attachEventListeners, 200);
    setTimeout(attachEventListeners, 500);
})();

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
            maxFiles: 1, // Only single file allowed
            acceptedFiles: ".zip,application/zip,application/x-zip-compressed,application/x-zip", // Only ZIP files
            addRemoveLinks: true,
            clickable: true,
            autoProcessQueue: false, // Don't upload automatically
            uploadMultiple: false, // Single file only
            dictDefaultMessage: "Drop tour ZIP file here or click to select",
            dictFileTooBig: "File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.",
            dictInvalidFileType: "Only ZIP files are allowed. Please upload a ZIP file containing tour assets.",
            dictMaxFilesExceeded: "Only one ZIP file is allowed. Please remove the existing file first.",
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
                    
                    // Validate file type - Only ZIP files allowed
                    const validZipTypes = ['application/zip', 'application/x-zip-compressed', 'application/x-zip', 'application/octet-stream'];
                    const isZipFile = file.name.toLowerCase().endsWith('.zip');
                    
                    if (!validZipTypes.includes(file.type) && !isZipFile) {
                        dropzone.removeFile(file);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Invalid File Type',
                                text: `${file.name} is not a ZIP file. Only ZIP files are allowed.`
                            });
                        } else {
                            alert(`File ${file.name} is not a ZIP file. Only ZIP files are allowed.`);
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
                        
                        // Show loading overlay with spinner
                        const loadingOverlay = document.getElementById('tour-loading-overlay');
                        const folderContainer = document.getElementById('folder-processing-container');
                        const currentFolderName = document.getElementById('current-folder-name');
                        const folderProgressBar = document.getElementById('folder-progress-bar');
                        const folderStatus = document.getElementById('folder-status');
                        
                        if (loadingOverlay) {
                            loadingOverlay.style.display = 'flex';
                        }
                        
                        // Show folder container
                        if (folderContainer) {
                            folderContainer.style.display = 'block';
                        }
                        
                        // Show loading state on button
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalBtnText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="ri-loader-4-line me-1"></i> Updating...';
                        
                        // List of folders to display
                        const folders = ['images', 'gallery', 'tiles', 'index.html', 'Json data'];
                        let currentIndex = 0;
                        let folderIntervalId = null;
                        
                        // Function to display folders in sequence
                        function startFolderDisplay() {
                            folderIntervalId = setInterval(() => {
                                
                                const folder = folders[currentIndex % folders.length];
                                
                                // Display folder name
                                if (currentFolderName) {
                                    currentFolderName.textContent = folder;
                                }
                                
                                if (folderStatus) {
                                    folderStatus.textContent = `Processing folder ${(currentIndex % folders.length) + 1} of ${folders.length}`;
                                }
                                
                                // Reset and animate progress bar
                                if (folderProgressBar) {
                                    folderProgressBar.style.width = '0%';
                                    let progress = 0;
                                    const progressInterval = setInterval(() => {
                                        progress += 2; // Increment by 2% every 100ms = 5 seconds total
                                        if (folderProgressBar) {
                                            folderProgressBar.style.width = progress + '%';
                                        }
                                        
                                        if (progress >= 100) {
                                            clearInterval(progressInterval);
                                        }
                                    }, 100);
                                }
                                
                                currentIndex++;
                            }, 5200); // 5 seconds per folder + 200ms pause
                        }
                        
                        // Start displaying folders immediately
                        startFolderDisplay();
                        
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
                            // Stop folder display animation
                            if (folderIntervalId) {
                                clearInterval(folderIntervalId);
                            }
                            
                            if (data.success) {
                                // Show completion message
                                if (folderStatus) folderStatus.textContent = 'Upload complete!';
                                if (folderProgressBar) folderProgressBar.style.width = '100%';
                                
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
                            
                            // Stop folder display animation
                            if (folderIntervalId) {
                                clearInterval(folderIntervalId);
                            }
                            
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
