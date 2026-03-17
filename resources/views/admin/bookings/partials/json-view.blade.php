<form action="{{ route('admin.tours.updateTourJson', $tour) }}" method="post" id="jsonUpdateForm">
    @csrf
    @method('PUT')
    <div class="mt-3">
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <div>
                <h4 class="mb-0">
                    <i class="ri-code-json-line me-2"></i>Booking JSON Data
                </h4>
                <small class="text-muted px-2">View and edit the complete JSON configuration for this tour booking</small>
            </div>
            <button type="button" class="btn btn-md btn-primary ms-3" id="editJsonBtn" data-bs-toggle="modal"
                data-bs-target="#jsonEditorModal">
                <i class="ri-edit-2-line me-1"></i>Edit JSON
            </button>
        </div>
        <textarea id="jsonTextAreaView" name="final_josn" class="w-100 bg-light p-3 rounded border" rows="25"
            readonly style="font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 12px;">
        {!! is_array($tour->final_json) ? json_encode($tour->final_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $tour->final_json !!}
        </textarea>
        <div class="d-flex justify-content-end my-3 gap-2">
            <button type="reset" class="btn btn-outline-secondary">
                <i class="ri-refresh-line me-1"></i>Reset
            </button>
            <button type="submit" id="jsonUpdatebtn" class="btn btn-success">
                <i class="ri-save-3-line me-1"></i>Save to Database
            </button>
        </div>
    </div>
</form>

<!-- json editor Modal in the Page using jsoneditor -->
<div class="modal fade w-100" id="jsonEditorModal" tabindex="-1" aria-labelledby="jsonEditorModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-4" id="jsonEditorModalLabel">
                    <i class="ri-code-s-slash-line me-2"></i>JSON Editor
                </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="jsoneditor" style="width: 100%; height: 550px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Close
                </button>
                <button type="button" id="jsonDataSaveBtn" class="btn btn-primary">
                    <i class="ri-check-line me-1"></i>Save changes
                </button>
            </div>
        </div>
    </div>
</div>

<!--  conformation changes modal -->
<div class="modal fade w-100" id="compareJsonModal" aria-hidden="true" aria-labelledby="compareJsonModalLabel" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="compareJsonModalLabel">
          <i class="ri-git-diff-line me-2"></i>Review Changes
        </h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="conpareJsonBody">
        <!-- Diff content will be inserted here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="jsonDontSaveBtn">
          <i class="ri-close-line me-1"></i>Discard Changes
        </button>
        <button type="button" class="btn btn-success" id="jsonSavebtn">
          <i class="ri-save-line me-1"></i>Confirm & Save
        </button>
      </div>
    </div>
  </div>
</div>


<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jsondiffpatch/0.3.11/jsondiffpatch.umd.js"
    integrity="sha512-1/tJGdBwOGJ3QrvU2diNgmqBQBVcc7ioLKVwagMZNP4/LfvtQo3yTyxAxxuRWzSrpbEShaWG9bk/WUwr4KG07g=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/jsondiffpatch/0.3.11/formatters-styles/annotated.css"
    integrity="sha512-g+Q5QP9G+qSkZ6YS9sYHxk0mUaf+nDOdr/UTqSSIvcYM8ETH93KWywztidF+e7o865rxh1VYl2beQxTbFxHGuA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
@vite('resources/js/pages/booking-edit-json-edit.js')