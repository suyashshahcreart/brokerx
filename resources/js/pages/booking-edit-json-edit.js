import $ from 'jquery';
import Swal from 'sweetalert2';
import 'jsoneditor/dist/jsoneditor.min.css';
import bootstrap from 'bootstrap/dist/js/bootstrap.min';
import JSONEditor from 'jsoneditor/dist/jsoneditor.min.js';
import * as jsondiffpatch from 'jsondiffpatch';

const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const buildDiffPreviewHtml = (delta) => {
    const htmlFormatter = jsondiffpatch?.formatters?.html;
    if (htmlFormatter && typeof htmlFormatter.format === 'function') {
        return htmlFormatter.format(delta);
    }
    // Fallback when html formatter bundle is not available.
    return `<pre class="text-start bg-light p-3 rounded">${escapeHtml(JSON.stringify(delta, null, 2))}</pre>`;
};

// Get references to the button and textarea
let editbutton = $('#editJsonBtn');
let jsonTextarea = $('#jsonTextAreaView');
let jsonEditorModal = new bootstrap.Modal(document.getElementById('jsonEditorModal'), {
    keyboard: false
});
let OriginalJsonData = jsonTextarea.val() ? JSON.parse(jsonTextarea.val()) : null;
// create the editor
const container = document.getElementById("jsoneditor")
const options = {
    mode: 'code',
    modes: ['code', 'tree', 'view'],
    search: true,
    navigationBar: true,
    onError: function (err) {
        alert(err.toString());
    }
}
const editor = new JSONEditor(container, options)

editbutton.on('click', function () {
    let josnData = jsonTextarea.val() ? JSON.parse(jsonTextarea.val()) : {};
    editor.set(josnData)
    OriginalJsonData = editor.get();
});
$('#jsonDataSaveBtn').on('click', function () {
    const updatedJson = editor.get()
    const delta = jsondiffpatch.diff(OriginalJsonData, updatedJson)
    if (!delta) {
        Swal.fire("No Changes Dected", "", "info");
        jsonEditorModal.hide();
        return;
    }
    const html = buildDiffPreviewHtml(delta);
    // alert of the changes and fonform it.
    Swal.fire({
        title: "Do you want to save the changes? please Conform the changes below",
        html: html,
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: "Save",
        denyButtonText: `Don't save`,
        width: '60%',
        heightAuto: false,
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire("Json Update Saved!", "", "success");
            jsonTextarea.val(JSON.stringify(updatedJson, null, 2));
            jsonEditorModal.hide();
        } else if (result.isDenied) {
            Swal.fire("Changes are not saved", "", "info");
            editor.set(OriginalJsonData)
            jsonEditorModal.hide();
        }
    });
});
