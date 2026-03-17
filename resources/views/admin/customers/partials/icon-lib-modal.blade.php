<!-- icon liberary Modal -->
<div class="modal fade mt-5rem" id="iconLiberaryModal" tabindex="-2" aria-labelledby="iconLiberatyModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    id="iconLibCloseBtn"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="iconLibSearch" placeholder="Search icons...">
                </div>
                <div class="mb-3 text-center">
                    <button class="btn btn-sm btn-outline-secondary w-100" id="iconLibNoIconBtn">
                        <span style="font-size:1.5rem;vertical-align:middle;">&#8709;</span> No Icon
                    </button>
                </div>
                <div id="iconLibGrid" class="row g-2" style="max-height:400px;overflow-y:auto;"></div>
            </div>
        </div>
    </div>
</div>
@vite(['resources/js/icon-lib.js'])