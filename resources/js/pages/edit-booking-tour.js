	// Footer brand logo preview
	var footerBrandLogoInput = document.getElementById('footer_brand_logo');
	var footerBrandLogoPreview = document.getElementById('footer_brand_logo_preview');
	if (footerBrandLogoInput && footerBrandLogoPreview) {
		footerBrandLogoInput.addEventListener('change', function (event) {
			const file = event.target.files[0];
			if (file) {
				const reader = new FileReader();
				reader.onload = function (e) {
					footerBrandLogoPreview.src = e.target.result;
					footerBrandLogoPreview.style.display = '';
				};
				reader.readAsDataURL(file);
			} else {
				footerBrandLogoPreview.src = '';
				footerBrandLogoPreview.style.display = 'none';
			}
		});
	}
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
