/**
 * Photographer Visit Job Check-in interactions.
 * Provides explicit camera open/close controls, capture/upload handling, and GPS detection.
 */

document.addEventListener('DOMContentLoaded', () => {
	const video = document.getElementById('camera-stream');
	const previewImage = document.getElementById('photo-preview');
	const canvas = document.getElementById('photo-canvas');
	const openCameraButton = document.getElementById('open-camera-btn');
	const captureButton = document.getElementById('capture-btn');
	const retakeButton = document.getElementById('retake-btn');
	const photoInput = document.getElementById('photo');
	const cameraStatus = document.getElementById('camera-status');

	const locationInput = document.getElementById('location');
	const detectLocationButton = document.getElementById('detect-location-btn');
	const locationStatus = document.getElementById('location-status');
	const locationTimestampHiddenInput = document.getElementById('location-timestamp');
	const locationAccuracyInput = document.getElementById('location-accuracy');
	const locationSourceInput = document.getElementById('location-source');

	if (!video || !canvas || !openCameraButton || !captureButton || !retakeButton || !photoInput) {
		return;
	}

	let mediaStream = null;
	let capturedObjectUrl = null;
	let cameraActive = false;

	const cameraSupported = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
	const geolocationSupported = 'geolocation' in navigator;
	const isLocalhost = ['localhost', '127.0.0.1', '0.0.0.0', '[::1]'].includes(window.location.hostname);
	const isBraveBrowser = typeof navigator.brave !== 'undefined';
	const GEO_OPTIONS = {
		enableHighAccuracy: true,
		timeout: 10000,
		maximumAge: 0
	};

	const OPEN_LABEL = '<i class="ri-vidicon-line me-1"></i>Open Camera';
	const CLOSE_LABEL = '<i class="ri-close-circle-line me-1"></i>Close Camera';

	const setCameraStatus = (message, tone = 'muted') => {
		if (!cameraStatus) {
			return;
		}
		cameraStatus.textContent = message;
		cameraStatus.classList.remove('text-muted', 'text-danger', 'text-success');
		cameraStatus.classList.add(`text-${tone}`);
	};

	const setLocationStatus = (message, tone = 'muted') => {
		if (!locationStatus) {
			return;
		}
		locationStatus.textContent = message;
		locationStatus.classList.remove('text-muted', 'text-danger', 'text-success');
		locationStatus.classList.add(`text-${tone}`);
	};

	const setOpenButtonState = active => {
		openCameraButton.innerHTML = active ? CLOSE_LABEL : OPEN_LABEL;
		openCameraButton.classList.toggle('btn-outline-danger', active);
		openCameraButton.classList.toggle('btn-outline-primary', !active);
		openCameraButton.dataset.state = active ? 'open' : 'closed';
	};

	const resetPreview = () => {
		if (capturedObjectUrl) {
			URL.revokeObjectURL(capturedObjectUrl);
			capturedObjectUrl = null;
		}
		previewImage.classList.add('d-none');
		previewImage.removeAttribute('src');
		retakeButton.classList.add('d-none');
	};

	const stopCamera = (keepStatus = false) => {
		if (mediaStream) {
			mediaStream.getTracks().forEach(track => track.stop());
			mediaStream = null;
		}
		cameraActive = false;
		video.classList.add('d-none');
		captureButton.disabled = true;
		setOpenButtonState(false);
		if (!keepStatus) {
			setCameraStatus('Camera idle. Click "Open Camera" to allow access.', 'muted');
		}
	};

	const assignFileToInput = file => {
		if (typeof DataTransfer === 'undefined') {
			setCameraStatus('Browser does not support direct camera uploads. Please use the upload option.', 'danger');
			return false;
		}
		const dataTransfer = new DataTransfer();
		dataTransfer.items.add(file);
		photoInput.files = dataTransfer.files;
		return true;
	};

	const startCamera = async () => {
		if (!cameraSupported) {
			setCameraStatus('Camera capture is not supported. Use the upload option instead.', 'danger');
			openCameraButton.disabled = true;
			captureButton.disabled = true;
			return;
		}
		if (cameraActive) {
			return;
		}
		try {
			openCameraButton.disabled = true;
			captureButton.disabled = true;
			setCameraStatus('Requesting camera access…', 'muted');
			mediaStream = await navigator.mediaDevices.getUserMedia({
				video: { facingMode: 'environment' },
				audio: false
			});
			video.srcObject = mediaStream;
			video.classList.remove('d-none');
			previewImage.classList.add('d-none');
			captureButton.disabled = false;
			cameraActive = true;
			setCameraStatus('Camera active. Capture a photo when ready.', 'success');
			setOpenButtonState(true);
		} catch (error) {
			console.error('Unable to access camera', error);
			setCameraStatus('Unable to access the camera. Check permissions or use the upload option.', 'danger');
			video.classList.add('d-none');
		} finally {
			openCameraButton.disabled = false;
		}
	};

	const capturePhoto = () => {
		if (!mediaStream) {
			setCameraStatus('Camera is not ready. Please allow access or use the upload option.', 'danger');
			return;
		}

		const track = mediaStream.getVideoTracks()[0];
		const settings = track?.getSettings?.() || {};
		const width = video.videoWidth || settings.width || 1280;
		const height = video.videoHeight || settings.height || 720;

		canvas.width = width;
		canvas.height = height;
		const context = canvas.getContext('2d');
		context.drawImage(video, 0, 0, width, height);

		canvas.toBlob(blob => {
			if (!blob) {
				setCameraStatus('Could not capture photo. Please try again.', 'danger');
				return;
			}

			const file = new File([blob], `check-in-${Date.now()}.jpg`, { type: 'image/jpeg' });
			if (!assignFileToInput(file)) {
				stopCamera();
				return;
			}

			if (capturedObjectUrl) {
				URL.revokeObjectURL(capturedObjectUrl);
			}
			capturedObjectUrl = URL.createObjectURL(blob);
			previewImage.src = capturedObjectUrl;
			previewImage.classList.remove('d-none');
			retakeButton.classList.remove('d-none');
			video.classList.add('d-none');
			setCameraStatus('Photo captured successfully. You can retake or submit.', 'success');
			openCameraButton.disabled = true;

			stopCamera(true);
		}, 'image/jpeg', 0.92);
	};

	const toggleCamera = () => {
		if (cameraActive) {
			stopCamera();
		} else if (!photoInput.files.length && previewImage.classList.contains('d-none')) {
			startCamera();
		} else {
			retakeButton.click();
		}
	};

	openCameraButton.addEventListener('click', toggleCamera);
	captureButton.addEventListener('click', capturePhoto);
	retakeButton.addEventListener('click', () => {
		photoInput.value = '';
		resetPreview();
		openCameraButton.disabled = false;
		setCameraStatus('Camera idle. Click "Open Camera" to allow access.', 'muted');
		startCamera();
	});

	if (!cameraSupported) {
		setCameraStatus('Camera capture is not supported. Use the upload option instead.', 'danger');
		captureButton.disabled = true;
		openCameraButton.disabled = true;
		setOpenButtonState(false);
	} else {
		captureButton.disabled = true;
		setCameraStatus('Camera idle. Click "Open Camera" to allow access.', 'muted');
		setOpenButtonState(false);
	}

	const getLocation = () => new Promise((resolve, reject) => {
		if (!('geolocation' in navigator)) {
			reject({ message: 'Geolocation is not supported by this browser.', code: null });
			return;
		}

		navigator.geolocation.getCurrentPosition(position => {
			resolve({
				lat: position.coords.latitude,
				lng: position.coords.longitude,
				accuracy: position.coords.accuracy,
				timestamp: position.timestamp,
				source: 'gps'
			});
		}, error => {
			let message = 'Unknown location error.';
			switch (error.code) {
				case error.PERMISSION_DENIED:
					message = 'Location permission denied by the browser.';
					break;
				case error.POSITION_UNAVAILABLE:
					message = 'Location information unavailable.';
					break;
				case error.TIMEOUT:
					message = 'Location request timed out.';
					break;
			}
			reject({ message, code: error.code });
		}, GEO_OPTIONS);
	});

	const watchLocation = () => new Promise((resolve, reject) => {
		if (!('geolocation' in navigator)) {
			reject({ message: 'Geolocation is not supported by this browser.', code: null });
			return;
		}

		const geo = navigator.geolocation;
		const watchId = geo.watchPosition(position => {
			geo.clearWatch(watchId);
			clearTimeout(timerId);
			resolve({
				lat: position.coords.latitude,
				lng: position.coords.longitude,
				accuracy: position.coords.accuracy,
				timestamp: position.timestamp,
				source: 'gps'
			});
		}, error => {
			geo.clearWatch(watchId);
			clearTimeout(timerId);
			let message = 'Unknown location error.';
			switch (error.code) {
				case error.PERMISSION_DENIED:
					message = 'Location permission denied by the browser.';
					break;
				case error.POSITION_UNAVAILABLE:
					message = 'Location information unavailable.';
					break;
				case error.TIMEOUT:
					message = 'Location request timed out.';
					break;
			}
			reject({ message, code: error.code });
		}, { ...GEO_OPTIONS, timeout: 15000 });

		const timerId = setTimeout(() => {
			geo.clearWatch(watchId);
			reject({ message: 'Location request timed out.', code: 3 });
		}, 16000);
	});

	// Fallback to an IP-based lookup when the browser cannot return GPS coordinates.
	const getNetworkApproxLocation = async () => {
		const supportsAbort = typeof AbortController !== 'undefined';
		const controller = supportsAbort ? new AbortController() : null;
		const timeoutId = supportsAbort ? setTimeout(() => controller.abort(), 8000) : null;
		try {
			const response = await fetch('https://ipapi.co/json/', {
				headers: { Accept: 'application/json' },
				...(controller ? { signal: controller.signal } : {})
			});
			if (!response.ok) {
				throw new Error('Network geolocation lookup failed.');
			}
			const data = await response.json();
			const lat = parseFloat(data.latitude);
			const lng = parseFloat(data.longitude);
			if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
				throw new Error('Network service did not return coordinates.');
			}
			return {
				lat,
				lng,
				accuracy: Number.isFinite(data.accuracy) ? data.accuracy : null,
				timestamp: Date.now(),
				source: 'network'
			};
		} finally {
			if (timeoutId) {
				clearTimeout(timeoutId);
			}
		}
	};

	const clearLocationFields = () => {
		if (locationInput) {
			locationInput.value = '';
		}
		if (locationTimestampHiddenInput) {
			locationTimestampHiddenInput.value = '';
		}
		if (locationAccuracyInput) {
			locationAccuracyInput.value = '';
		}
		if (locationSourceInput) {
			locationSourceInput.value = '';
		}
	};

	const applyLocationResult = details => {
		const { lat, lng, accuracy, timestamp, source } = details;
		const formattedLocation = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
		const isoTimestamp = new Date(timestamp).toISOString();
		const roundedAccuracy = Number.isFinite(accuracy) ? Math.round(accuracy) : null;

		if (locationInput) {
			locationInput.value = formattedLocation;
		}
		if (locationTimestampHiddenInput) {
			locationTimestampHiddenInput.value = isoTimestamp;
		}
		if (locationAccuracyInput) {
			locationAccuracyInput.value = roundedAccuracy ?? '';
		}
		if (locationSourceInput) {
			locationSourceInput.value = source;
		}

		return { source };
	};

	const requestLocation = async () => {
		clearLocationFields();

		if (!geolocationSupported) {
			setLocationStatus('Geolocation is not supported on this device. Use a GPS-enabled device or contact support.', 'danger');
			return;
		}

		if (!window.isSecureContext && !isLocalhost) {
			setLocationStatus('Browser blocks GPS on insecure connections. Use HTTPS or access the site from localhost.', 'danger');
			return;
		}

		try {
			const permissionStatus = await navigator.permissions?.query?.({ name: 'geolocation' }).catch(() => null);
			if (permissionStatus?.state === 'denied') {
				setLocationStatus('Location access is blocked. Update browser settings to allow location for this site.', 'danger');
				return;
			}
		} catch (permissionError) {
			console.warn('Unable to verify geolocation permission status.', permissionError);
		}

		setLocationStatus('Fetching current location…', 'muted');

		try {
			const result = await getLocation();
			const { source } = applyLocationResult(result);
			const isNetworkSource = source === 'network';
			setLocationStatus(isNetworkSource ? 'Approximate location captured successfully.' : 'Location captured successfully.', isNetworkSource ? 'warning' : 'success');
			return;
		} catch (initialError) {
			console.warn('Geolocation error', initialError);
			if (initialError.code === 1) {
				const braveHint = isBraveBrowser ? ' In Brave, open the Shields icon and allow Location or enable Google Location Service.' : '';
				setLocationStatus(`Location permission denied. Update browser settings to allow location.${braveHint}`, 'danger');
				return;
			}

			let lastError = initialError;
			if (initialError.code === 2 || initialError.code === 3) {
				setLocationStatus('Trying high-accuracy retry…', 'muted');
				try {
					const watchResult = await watchLocation();
					const { source } = applyLocationResult(watchResult);
					const isNetworkSource = source === 'network';
					setLocationStatus(isNetworkSource ? 'Approximate location captured after retry.' : 'Location captured after retry.', isNetworkSource ? 'warning' : 'success');
					return;
				} catch (retryError) {
					lastError = retryError;
					console.warn('Geolocation retry error', retryError);
				}
			}

			try {
				setLocationStatus('Attempting network-based approximate location…', 'muted');
				const networkResult = await getNetworkApproxLocation();
				applyLocationResult(networkResult);
				setLocationStatus('Approximate location captured via network lookup.', 'warning');
				return;
			} catch (networkError) {
				console.warn('Network geolocation error', networkError);
			}

			const braveHint = isBraveBrowser ? ' If you are using Brave, allow location in Shields or enable Google Location Service.' : '';
			setLocationStatus(`${lastError.message || 'Unable to capture location automatically.'}${braveHint}`, 'danger');
		}
	};

	detectLocationButton?.addEventListener('click', requestLocation);

	// Form validation before submission
	const form = document.querySelector('form[enctype="multipart/form-data"]');
	if (form) {
		form.addEventListener('submit', (event) => {
			let hasErrors = false;
			const errors = [];

			// Validate location is provided
			if (!locationInput || !locationInput.value.trim()) {
				hasErrors = true;
				errors.push('Location is required. Please click "Use GPS" to capture your location.');
				setLocationStatus('Location is required for check-in/check-out.', 'danger');
			}

			// Validate photo is provided
			if (!photoInput || !photoInput.files || photoInput.files.length === 0) {
				hasErrors = true;
				errors.push('Photo is required. Please open the camera and capture a photo.');
				setCameraStatus('Photo is required for check-in/check-out.', 'danger');
			}

			if (hasErrors) {
				event.preventDefault();
				
				// Display error messages
				const existingAlert = form.querySelector('.alert-danger');
				if (existingAlert) {
					existingAlert.remove();
				}

				const alertDiv = document.createElement('div');
				alertDiv.className = 'alert alert-danger';
				alertDiv.innerHTML = '<ul class="mb-0">' + errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
				form.insertBefore(alertDiv, form.firstChild);

				// Scroll to the alert
				alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
				
				return false;
			}
		});
	}

	window.addEventListener('beforeunload', () => stopCamera());
});
