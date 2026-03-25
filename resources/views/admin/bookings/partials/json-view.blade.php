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
        <input type="text" readonly hidden name="diff_json" id="diffJsonInput">
        <div class="d-flex justify-content-end my-3 gap-2">
            <button type="reset" class="btn btn-outline-secondary">
                <i class="ri-refresh-line me-1"></i>Reset
            </button>
            <button type="submit" id="jsonUpdatebtn" class="btn btn-success">
                <i class="ri-save-3-line me-1"></i>Updaet JSON
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
            <div class="modal-body p-0">
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
          <i class="ri-git-diff-line me-2"></i> Review Changes
        </h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="conpareJsonBody" >
            <!-- Diff content will be inserted here -->
        </div>
        <div>
              <small class="text-muted">Review the differences between the original and edited JSON data before confirming your changes.
                <br> This Changes Save Only in the Local Editor, You Need to Confirm & Save to Update the Booking JSON Data Permanently.
              </small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="jsonDontSaveBtn">
          <i class="ri-close-line me-1"></i>Discard Changes
        </button>
        <button type="button" class="btn btn-primary" id="jsonSavebtn">
          <i class="ri-check-line me-1"></i>Confirm Changes
        </button>
      </div>
    </div>
  </div>
</div>
@vite('resources/js/pages/booking-edit-json-edit.js')