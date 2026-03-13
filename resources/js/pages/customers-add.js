import $ from 'jquery';

function createSocialLinkRowTour(platform = '', url = '', typeValue = '') {
    const row = $(`
        <div class="row g-2 align-items-center social-link-row">
            <div class="col-md-3">
                <input type="text" class="form-control social-platform" placeholder="e.g, facebook" value="">
                <input type="text" class="d-none social-type" value="fontawsome-icon">
            </div>
            <div class="col-md-5">
                <input type="url" class="form-control social-url"  placeholder="e.g, https://..." value="">
            </div>
            <div class="col-md-2 d-flex align-items-center gap-2">
                <input type="text" class="form-control social-type-value" placeholder="e.g, fa-solid fa-home" value="" readonly style="cursor:pointer;">
                <span class="icon-preview"></span>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm btnRemoveSocialLink" title="Remove">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    `);

    row.find('.social-platform').val(platform || '');
    row.find('.social-url').val(url || '');
    row.find('.social-type-value').val(typeValue || '');
    // Render icon preview if value exists
    if (typeValue) {
        row.find('.icon-preview').html(`<i class="${typeValue}"></i>`);
    }
    // Listen for value change to update preview
    row.find('.social-type-value').on('input', function () {
        const val = $(this).val();
        const preview = $(this).closest('.d-flex').find('.icon-preview');
        if (val) {
            preview.html(`<i class="${val}"></i>`);
        } else {
            preview.html('');
        }
    });
    return row;
}

$(document).on('click', '#add-social-link', function () {
    $('#social-links-container').append(createSocialLinkRowTour());
});
$(document).on('click', '.btnRemoveSocialLink', function () {
    const rows = $('#social-links-container .social-link-row');
    if (rows.length <= 1) {
        $(this).closest('.social-link-row').find('.social-platform, .social-url').val('');
    } else {
        $(this).closest('.social-link-row').remove();
    }
});
// Update input names based on platform value
$(document).on("change", ".social-platform", function(){
    const row = $(this).closest(".social-link-row");
    const platform = $(this).val().trim();
    if(!platform) return;
    row.find(".social-type")
        .attr("name", `social_link[${platform}][type]`);
    row.find(".social-url")
        .attr("name", `social_link[${platform}][link]`);
    row.find(".social-type-value")
        .attr("name", `social_link[${platform}][value]`);
});