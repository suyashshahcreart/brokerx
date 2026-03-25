import $ from 'jquery';
import Swal from 'sweetalert2';
import bootstrap from 'bootstrap/dist/js/bootstrap.min';
/* jsoneditor imports */
import JSONEditor from 'jsoneditor/dist/jsoneditor.min.js';
import 'jsoneditor/dist/jsoneditor.min.css';
/* jsondiffpatch and html formatter imports */
import * as jsondiffpatch from 'jsondiffpatch';
import { format as htmlFormat } from 'jsondiffpatch/formatters/html'
import 'jsondiffpatch/formatters/styles/html.css'

/**
 * Escape HTML characters for safe display
 */
const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

/**
 * JSON Editor State Management
 */
const jsonEditorState = {
    originalJson: null,
    updatedJson: null,
    isModified: false
};

// main form
const mainForm = $('#jsonUpdateForm');

// DOM Elements
const elements = {
    editButton: $('#editJsonBtn'),
    jsonTextarea: $('#jsonTextAreaView'),
    jsonDataSaveBtn: $('#jsonDataSaveBtn'),
    compareJsonContainer: $('#conpareJsonBody'),
    jsonSaveBtn: $('#jsonSavebtn'),
    diffJsonInput: $('#diffJsonInput'),
    jsonDontSaveBtn: $('#jsonDontSaveBtn'),
    jsonUpdateBtn: $('#jsonUpdatebtn')
};

// Modal Instances
const modals = {
    editor: new bootstrap.Modal(document.getElementById('jsonEditorModal'), {
        keyboard: false
    }),
    compare: new bootstrap.Modal(document.getElementById('compareJsonModal'), {
        keyboard: false
    })
};

// JSON Editor Configuration
const editorContainer = document.getElementById("jsoneditor");
const editorOptions = {
    mode: 'code',
    modes: ['code', 'tree', 'view'],
    search: true,
    navigationBar: true,
    onError: function (err) {
        Swal.fire({
            icon: 'error',
            title: 'JSON Editor Error',
            text: err.toString(),
            confirmButtonColor: '#dc3545'
        });
    }
};

// Initialize JSON Editor
const editor = new JSONEditor(editorContainer, editorOptions);

/**
 * Build HTML representation for diff preview
 */
const buildDiffPreviewHtml = (delta) => {
    const htmlFormatter = htmlFormat;
    if (htmlFormatter && typeof htmlFormatter === 'function') {
        return htmlFormatter(delta);
    }
    // Fallback when html formatter bundle is not available
    return `<pre class="text-start bg-light p-3 rounded border">${escapeHtml(JSON.stringify(delta, null, 2))}</pre>`;
};

/**
 * Handle Edit Button Click - Opens Editor Modal
 */
elements.editButton.on('click', function () {
    try {
        const currentJson = elements.jsonTextarea.val() ? JSON.parse(elements.jsonTextarea.val()) : {};
        jsonEditorState.originalJson = JSON.parse(JSON.stringify(currentJson)); // Deep copy
        editor.set(currentJson);
        jsonEditorState.isModified = false;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid JSON',
            text: 'Could not parse current JSON data',
            confirmButtonColor: '#dc3545'
        });
    }
});

/**
 * Handle JSON Editor Save Button Click
 * Validates changes and shows comparison modal
 */
elements.jsonDataSaveBtn.on('click', function () {
    try {
        jsonEditorState.updatedJson = editor.get();

        // Check if there are actual changes
        const delta = jsondiffpatch.diff(jsonEditorState.originalJson, jsonEditorState.updatedJson);

        if (!delta) {
            Swal.fire({
                icon: 'info',
                title: 'No Changes Detected',
                text: 'The JSON content is identical to the original',
                confirmButtonColor: '#17a2b8'
            });
            return;
        }
        
        console.log('Delta:', delta); // Debug log for delta
        elements.diffJsonInput.val(JSON.stringify(delta)); // Store diff for potential future use

        // Store modification flag
        jsonEditorState.isModified = true;

        // Build and display diff
        const diffHtml = buildDiffPreviewHtml(delta);
        elements.compareJsonContainer.html(diffHtml);

        // Transition: Hide editor modal, show comparison modal
        modals.editor.hide();

        // Use modal event to ensure proper state transition
        const editorModalElement = document.getElementById('jsonEditorModal');
        editorModalElement.addEventListener('hidden.bs.modal', () => {
            modals.compare.show();
        }, { once: true });

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error Processing JSON',
            text: error.message,
            confirmButtonColor: '#dc3545'
        });
    }
});

/**
 * Handle Save Button in Comparison Modal
 * Updates textarea and closes modals
 */
elements.jsonSaveBtn.on('click', function () {
    try {
        if (!jsonEditorState.updatedJson) {
            throw new Error('No updated JSON data available');
        }

        // Update the textarea with formatted JSON
        const formattedJson = JSON.stringify(jsonEditorState.updatedJson, null, 2);
        elements.jsonTextarea.val(formattedJson);

        // Close compare modal
        modals.compare.hide();

        // Show success notification
        Swal.fire({
            icon: 'info',
            title: 'Local JSON Updated',
            html: 'Changes have been loaded. Click "<b>Update JSON</b>" button to save to database.',
            confirmButtonColor: '#28a745',
        });

        // Reset state
        jsonEditorState.isModified = false;
        jsonEditorState.updatedJson = null;

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error Saving Changes',
            text: error.message,
            confirmButtonColor: '#dc3545'
        });
    }
});

/**
 * Handle Don't Save Button in Comparison Modal
 * Returns to editor without applying changes
 */
elements.jsonDontSaveBtn.on('click', function () {
    // Reset the editor to original state
    if (jsonEditorState.originalJson) {
        editor.set(jsonEditorState.originalJson);
    }

    // Reset state
    jsonEditorState.isModified = false;
    jsonEditorState.updatedJson = null;

    // Close compare modal
    modals.compare.hide();

    // Use modal event to ensure proper state transition
    const compareModalElement = document.getElementById('compareJsonModal');
    compareModalElement.addEventListener('hidden.bs.modal', () => {
        modals.editor.show();
    }, { once: true });

    // Show info notification
    Swal.fire({
        icon: 'info',
        title: 'Changes Discarded',
        text: 'Returned to the JSON editor',
        confirmButtonColor: '#17a2b8',
        timer: 2000,
        timerProgressBar: true
    });
});

/**
 * Initialize - Called on page load to ensure clean state
 */
document.addEventListener('DOMContentLoaded', function () {
    // Reset all state on page load
    jsonEditorState.originalJson = null;
    jsonEditorState.updatedJson = null;
    jsonEditorState.isModified = false;
});
