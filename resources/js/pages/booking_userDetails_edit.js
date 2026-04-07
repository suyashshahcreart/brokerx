import $ from 'jquery';
window.$ = window.jQuery = $;
import '../../css/pages/materialIconLiberaryStyles.css';
import { IconLibrary } from '../materialIconLiberary';
import { materialIconList } from '../data/materialIconsList';
import Quill from 'quill';
import 'quill/dist/quill.core.css';
import 'quill/dist/quill.snow.css';
import { v4 as uuidv4 } from 'uuid';


const iconLib = new IconLibrary({ materialIconList });
const quillEditors = {};

const languageMap = {
    en: 'English',
    gu: 'Gujarati',
    hi: 'Hindi'
};

function getEnabledLanguages() {
    const enabled = window.enabledLanguages || ['en'];
    return enabled.map(code => ({
        code,
        label: languageMap[code] || code.toUpperCase()
    }));
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function parseUserDetailsData() {
    const raw = $('#userDetailsContainer').attr('data-userdetails-data');
    if (!raw) {
        return [];
    }

    if (typeof raw === 'object') {
        return raw;
    }

    try {
        return JSON.parse(raw);
    } catch (error) {
        console.warn('Unable to parse user details data:', error);
        return [];
    }
}

$(document).ready(function () {
    iconLib.init('materialIconModal', 'materialIconSearch');

    $('#userDetailsButtonIcon').on('click', function () {
        iconLib.open(this, $('#iconPreview_userDetailsButtonIcon'));
    });

    initializeUserDetails();
});

function initializeUserDetails() {
    const addUserDetailsBtn = $('#addUserDetailsBtn');
    const existingData = parseUserDetailsData();

    if (Array.isArray(existingData) && existingData.length) {
        existingData.forEach(detail => addUserDetailsRow(detail));
    }

    addUserDetailsBtn.on('click', function () {
        addUserDetailsRow();
    });
}

function initQuillForRow(rowIndex, initialHtml = '') {
    const editorId = `descraptionQuillEditor_${rowIndex}`;
    const hiddenInput = document.getElementById(`descraptionHidden_${rowIndex}`);
    if (!hiddenInput || quillEditors[rowIndex]) {
        return;
    }
    const quill = new Quill(`#${editorId}`, {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ header: [1, 2, 3, false] }],
                ['blockquote', 'code-block'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ color: [] }, { background: [] }],
                ['link', 'clean']
            ]
        }
    });

    if (initialHtml) {
        quill.setText(initialHtml);
        hiddenInput.value = initialHtml;
    }

    quill.on('text-change', function () {
        hiddenInput.value = quill.getText();
    });

    quillEditors[rowIndex] = quill;
}

function addUserDetailsRow(detail = {}) {
    const container = document.getElementById('userDetailsContainer');
    if (!container) {
        return;
    }

    const existingRows = Array.from(container.querySelectorAll('.user-detail-row'));
    const rowIndex = existingRows.length
        ? Math.max(...existingRows.map(row => Number(row.dataset.rowIndex) || 0)) + 1
        : 0;

    const idValue = escapeHtml(detail.id ?? uuidv4());
    const icon = escapeHtml(detail.icon ?? '');
    const title = escapeHtml(detail.title ?? '');
    const summery = escapeHtml(detail.summery ?? '');
    const descraption = detail.description ?? '';
    const descraptionHiddenValue = escapeHtml(descraption);

    const rowHTML = `
        <div class="user-detail-row row mb-3 border p-3 rounded" data-row-index="${rowIndex}">
            <div class="col-md-2">
                <label class="form-label">ID</label>
                <input type="text" name="user_details[${rowIndex}][id]" class="form-control" value="${idValue}" placeholder="Unique ID">
            </div>

            <div class="col-md-2">
                <label class="form-label">Icon</label>
                <div class="input-group">
                    <input type="text" name="user_details[${rowIndex}][icon]" class="form-control icon-input" placeholder="Click to select" readonly value="${icon}">
                    <div class="icon-preview" id="iconPreview_${rowIndex}">
                        ${icon ? `<div class="icon-item text-center"><span class="material-icons-outlined">${icon}</span></div>` : ''}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Title</label>
                <input type="text" name="user_details[${rowIndex}][title]" class="form-control" value="${title}" placeholder="Title">
            </div>

            <div class="col-md-4">
                <label class="form-label">Summary</label>
                <textarea name="user_details[${rowIndex}][summery]" class="form-control" rows="2" placeholder="Summary">${summery}</textarea>
            </div>

            <div class="col-md-12 mt-3">
                <label class="form-label">Description</label>
                <div class="mb-3">
                    <div id="descraptionQuillEditor_${rowIndex}" class="quill-editor" style="min-height: 220px; border: 1px solid #ced4da; border-radius: .25rem; background: #fff; margin-bottom: 1rem;">${descraption}</div>
                </div>
                <input type="hidden" name="user_details[${rowIndex}][descraption]" id="descraptionHidden_${rowIndex}" value="${descraptionHiddenValue}">
            </div>

            <div class="col-md-12 d-flex justify-content-end">
                <button type="button" class="btn btn-danger btn-sm remove-user-detail" title="Remove">
                    <i class="ri-delete-bin-line"></i> Remove
                </button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', rowHTML);

    const newRow = container.querySelector('.user-detail-row:last-child');

    const removeBtn = newRow.querySelector('.remove-user-detail');
    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            if (quillEditors[rowIndex]) {
                delete quillEditors[rowIndex];
            }
            newRow.remove();
        });
    }

    const iconInput = newRow.querySelector('.icon-input');
    if (iconInput) {
        iconInput.addEventListener('click', function () {
            iconLib.open(this, $(`#iconPreview_${rowIndex}`));
        });
    }

    initQuillForRow(rowIndex, descraption);
}
