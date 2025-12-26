	// Unified logo preview logic for Footer Logo and Footer Brand Logo
	document.addEventListener('DOMContentLoaded', function () {
	    function setupLogoPreview(inputId, previewId) {
		var input = document.getElementById(inputId);
		var preview = document.getElementById(previewId);
		if (input && preview) {
		    // Store the original src for fallback
		    if (!preview.getAttribute('data-original-src')) {
			preview.setAttribute('data-original-src', preview.src);
		    }
		    input.addEventListener('change', function (event) {
			const file = event.target.files[0];
			if (file) {
			    const reader = new FileReader();
			    reader.onload = function (e) {
				preview.src = e.target.result;
				preview.style.display = '';
			    };
			    reader.readAsDataURL(file);
			} else {
			    // If no file selected, revert to original server image if available
			    const originalSrc = preview.getAttribute('data-original-src');
			    if (originalSrc) {
				preview.src = originalSrc;
				preview.style.display = '';
			    } else {
				preview.src = '';
				preview.style.display = 'none';
			    }
			}
		    });
		}
	    }
	    setupLogoPreview('footer_logo', 'footer_logo_preview');
	    setupLogoPreview('footer_brand_logo', 'footer_brand_logo_preview');
	});
// Sidebar Footer Link Show logic
document.addEventListener('DOMContentLoaded', function () {
	var linkShowSelect = document.getElementById('sidebar_footer_link_show');
	var sidebarFooterFields = document.getElementById('sidebar-footer-fields');
	if (linkShowSelect && sidebarFooterFields) {
		function toggleSidebarFooterFields() {
			if (linkShowSelect.value === '1') {
				sidebarFooterFields.style.display = '';
			} else {
				sidebarFooterFields.style.display = 'none';
			}
		}
		linkShowSelect.addEventListener('change', toggleSidebarFooterFields);
		toggleSidebarFooterFields(); // Initial state
	}

	// Sidebar logo preview
	var sidebarLogoInput = document.getElementById('custom_logo_sidebar');
	var sidebarLogoPreview = document.getElementById('sidebar_logo_preview');
	if (sidebarLogoInput && sidebarLogoPreview) {
		sidebarLogoInput.addEventListener('change', function (event) {
			const file = event.target.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function (e) {
					sidebarLogoPreview.src = e.target.result;
					sidebarLogoPreview.style.display = '';
				};
				reader.readAsDataURL(file);
			} else {
				sidebarLogoPreview.src = '';
				sidebarLogoPreview.style.display = 'none';
			}
		});
	}
});
