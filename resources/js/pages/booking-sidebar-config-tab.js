import { syncTourLanguageTabs } from '../utils/tour-language-tabs';
import reinitalizeEditors from '../tinyEditor';
import initNavigateNodeFields from '../utils/sidebar-navigate-node-preview';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('sidebarConfigTabUpdateForm');
    if (!form) {
        return;
    }

    const logoInput = document.getElementById('sidebar_config_logo');
    const logoPreview = document.getElementById('sidebar_config_logo_preview');
    const removeLogoCheckbox = document.getElementById('remove_sidebar_config_logo');

    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                logoPreview.src = e.target?.result || '';
                logoPreview.style.display = '';
            };
            reader.readAsDataURL(file);
            if (removeLogoCheckbox) {
                removeLogoCheckbox.checked = false;
            }
        });
    }

    if (removeLogoCheckbox && logoPreview) {
        removeLogoCheckbox.addEventListener('change', () => {
            if (removeLogoCheckbox.checked) {
                logoPreview.src = '';
                logoPreview.style.display = 'none';
                if (logoInput) {
                    logoInput.value = '';
                }
            }
        });
    }

    form.querySelectorAll('.sidebar-tag-color-picker').forEach((picker) => {
        picker.addEventListener('input', () => {
            const targetId = picker.getAttribute('data-target');
            const textInput = targetId ? document.getElementById(targetId) : null;
            if (textInput) {
                textInput.value = picker.value;
            }
        });
    });

    form.querySelectorAll('.sidebar-tag-color-text').forEach((textInput) => {
        textInput.addEventListener('input', () => {
            const id = textInput.id;
            const picker = form.querySelector(`.sidebar-tag-color-picker[data-target="${id}"]`);
            if (picker) {
                picker.value = textInput.value;
            }
        });
    });

    const syncTagSlot = (slotEl) => {
        const prefix = slotEl.getAttribute('data-tag-prefix');
        if (!prefix) {
            return;
        }

        const showToggle = slotEl.querySelector('.sidebar-tag-show-toggle');
        const fieldsWrap = slotEl.querySelector('.sidebar-tag-fields');
        const clickableToggle = slotEl.querySelector('.sidebar-tag-clickable-toggle');
        const actionWrap = slotEl.querySelector('.sidebar-tag-action-wrap');

        const syncShow = () => {
            const on = showToggle?.checked;
            fieldsWrap?.classList.toggle('d-none', !on);
        };

        const syncClickable = () => {
            const on = clickableToggle?.checked;
            actionWrap?.classList.toggle('d-none', !on);
        };

        const syncActionSections = () => {
            const selected = slotEl.querySelector(`input[name="${prefix}_action"]:checked`)?.value;
            slotEl.querySelectorAll('.sidebar-tag-action-section').forEach((section) => {
                section.classList.toggle('d-none', section.getAttribute('data-action') !== selected);
            });
        };

        showToggle?.addEventListener('change', syncShow);
        clickableToggle?.addEventListener('change', syncClickable);
        slotEl.querySelectorAll('.sidebar-tag-action-radio').forEach((radio) => {
            radio.addEventListener('change', syncActionSections);
        });

        syncShow();
        syncClickable();
        syncActionSections();

        const imagesHidden = slotEl.querySelector(`#${prefix}_existing_images_json`);
        const imagesPreview = slotEl.querySelector(`#${prefix}_images_preview`);

        const persistImagesJson = () => {
            if (!imagesHidden || !imagesPreview) {
                return;
            }
            const payload = [];
            imagesPreview.querySelectorAll('.sidebar-tag-image-item').forEach((item) => {
                const url = item.getAttribute('data-url');
                const fileName = item.getAttribute('data-file-name') || '';
                if (url) {
                    payload.push({ url, fileName });
                }
            });
            imagesHidden.value = JSON.stringify(payload);
        };

        imagesPreview?.addEventListener('click', (event) => {
            const btn = event.target.closest('.sidebar-tag-image-remove');
            if (!btn) {
                return;
            }
            btn.closest('.sidebar-tag-image-item')?.remove();
            persistImagesJson();
        });

        persistImagesJson();
    };

    const initMediaActionFields = (slotEl) => {
        slotEl.querySelectorAll('.sidebar-tag-media-action-fields').forEach((wrap) => {
            if (wrap.dataset.mediaInit === '1') {
                return;
            }
            wrap.dataset.mediaInit = '1';

            const mediaType = wrap.getAttribute('data-media-type') || 'video';
            const prefix = wrap.getAttribute('data-media-prefix') || '';
            const isVideo = mediaType === 'video';
            const urlInput = wrap.querySelector('.sidebar-tag-media-url-input');
            const urlRemoveBtn = wrap.querySelector('.sidebar-tag-media-url-remove');
            const fileInput = wrap.querySelector('.sidebar-tag-media-file-input');
            const chooseBtn = wrap.querySelector('.sidebar-tag-media-choose-btn');
            const existingHidden = wrap.querySelector('.sidebar-tag-media-existing-json');
            const previewWrap = wrap.querySelector('.sidebar-tag-media-preview');
            const validationMsg = wrap.querySelector('.sidebar-tag-media-validation');
            const urlLabel = wrap.querySelector('.sidebar-tag-media-url-label');
            const chooseLabel = wrap.querySelector('.sidebar-tag-media-choose-label');

            const persistExistingJson = (payload) => {
                if (!existingHidden) {
                    return;
                }
                existingHidden.value = payload ? JSON.stringify(payload) : '';
            };

            const readExistingJson = () => {
                if (!existingHidden?.value || existingHidden.value === 'null') {
                    return null;
                }
                try {
                    const parsed = JSON.parse(existingHidden.value);
                    return parsed?.url ? parsed : null;
                } catch {
                    return null;
                }
            };

            const hasUrl = () => !!(urlInput?.value?.trim());
            const hasFile = () => {
                if (fileInput?.files?.length) {
                    return true;
                }
                const existing = readExistingJson();
                return !!(existing?.url && !String(existing.url).startsWith('blob:'));
            };
            const hasMedia = () => hasUrl() || hasFile();

            const syncValidation = () => {
                const empty = !hasMedia();
                validationMsg?.classList.toggle('d-none', !empty);
                urlInput?.classList.toggle('border-danger', empty);
                chooseBtn?.classList.toggle('border-danger', empty);
                chooseBtn?.classList.toggle('text-danger', empty);
                urlLabel?.classList.toggle('text-danger', empty);
                chooseLabel?.classList.toggle('text-danger', empty);
            };

            const syncUrlControls = () => {
                const fileSelected = hasFile();
                if (urlInput) {
                    urlInput.disabled = fileSelected;
                }
                if (chooseBtn && fileInput) {
                    chooseBtn.classList.toggle('disabled', hasUrl());
                    chooseBtn.style.pointerEvents = hasUrl() ? 'none' : '';
                    chooseBtn.style.opacity = hasUrl() ? '0.65' : '';
                }
                urlRemoveBtn?.classList.toggle('d-none', !hasUrl());
                syncValidation();
            };

            const renderPreview = (src, fileName, url, isBlob) => {
                if (!previewWrap) {
                    return;
                }
                const iconClass = isVideo ? 'ri-video-line' : 'ri-file-text-line';
                const thumbHtml = isVideo
                    ? `<video src="${src}" class="w-100 h-100" style="object-fit:cover;" muted playsinline preload="metadata"></video>`
                    : `<i class="${iconClass} fs-2 text-secondary"></i>`;

                previewWrap.innerHTML = `
                    <div class="d-flex align-items-center gap-3 sidebar-tag-media-preview-item"
                        data-url="${url || ''}"
                        data-file-name="${fileName || ''}"
                        data-is-blob="${isBlob ? '1' : '0'}">
                        <div class="sidebar-tag-media-preview-thumb border rounded bg-light d-flex align-items-center justify-content-center"
                            style="width:120px;height:80px;overflow:hidden;">
                            ${thumbHtml}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="text-truncate fw-semibold sidebar-tag-media-preview-name" title="${fileName || ''}">
                                ${fileName || ''}
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger sidebar-tag-media-file-remove" title="Remove uploaded file">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                `;
                previewWrap.classList.remove('d-none');
            };

            const clearPreview = () => {
                if (previewWrap) {
                    previewWrap.innerHTML = '';
                    previewWrap.classList.add('d-none');
                }
                persistExistingJson(null);
                if (fileInput) {
                    fileInput.value = '';
                }
                syncUrlControls();
            };

            urlInput?.addEventListener('input', () => {
                if (hasUrl()) {
                    clearPreview();
                }
                syncUrlControls();
            });

            urlRemoveBtn?.addEventListener('click', () => {
                if (urlInput) {
                    urlInput.value = '';
                }
                syncUrlControls();
            });

            fileInput?.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                if (!file) {
                    return;
                }
                if (isVideo && file.type && !file.type.startsWith('video/')) {
                    fileInput.value = '';
                    return;
                }
                if (urlInput) {
                    urlInput.value = '';
                }
                persistExistingJson(null);
                const blobUrl = URL.createObjectURL(file);
                renderPreview(blobUrl, file.name, '', true);
                syncUrlControls();
            });

            previewWrap?.addEventListener('click', (event) => {
                const btn = event.target.closest('.sidebar-tag-media-file-remove');
                if (!btn) {
                    return;
                }
                clearPreview();
            });

            syncUrlControls();
        });
    };

    const buildNodeOptionsHtml = (slotEl) => {
        const nodeSelect = slotEl.querySelector('.sidebar-tag-node-select');
        if (!nodeSelect) {
            return '<option value="">Select scene</option>';
        }
        return nodeSelect.innerHTML;
    };

    const persistMenuItemsJson = (slotEl) => {
        const prefix = slotEl.getAttribute('data-tag-prefix');
        if (!prefix) {
            return;
        }
        const hidden = slotEl.querySelector(`#${prefix}_menu_items_json`);
        const list = slotEl.querySelector(`#${prefix}_menu_items_list`);
        if (!hidden || !list) {
            return;
        }

        const items = [];
        list.querySelectorAll('.sidebar-tag-menu-item-row').forEach((row, index) => {
            const title = row.querySelector('.sidebar-tag-menu-title')?.value?.trim() || '';
            const nodeId = row.querySelector('.sidebar-tag-menu-node')?.value?.trim() || '';
            if (!title || !nodeId) {
                return;
            }
            items.push({
                id: row.getAttribute('data-item-id') || `bm-${Date.now()}-${index}`,
                title,
                nodeId,
                order: index,
            });
        });

        hidden.value = JSON.stringify(items);

        const emptyMsg = list.querySelector('.sidebar-tag-menu-empty');
        if (emptyMsg) {
            emptyMsg.classList.toggle('d-none', items.length > 0);
        }
    };

    const initMenuItemsEditor = (slotEl) => {
        const prefix = slotEl.getAttribute('data-tag-prefix');
        if (!prefix) {
            return;
        }

        const list = slotEl.querySelector(`#${prefix}_menu_items_list`);
        const addBtn = slotEl.querySelector('.sidebar-tag-menu-add-btn');
        if (!list || !addBtn) {
            return;
        }

        const nodeOptionsHtml = buildNodeOptionsHtml(slotEl);

        addBtn.addEventListener('click', () => {
            let emptyMsg = list.querySelector('.sidebar-tag-menu-empty');
            if (emptyMsg) {
                emptyMsg.classList.add('d-none');
            } else if (list.querySelectorAll('.sidebar-tag-menu-item-row').length === 0) {
                emptyMsg = document.createElement('p');
                emptyMsg.className = 'text-muted small sidebar-tag-menu-empty mb-0 d-none';
                emptyMsg.textContent = 'No menu items yet. Click "Add menu item".';
                list.appendChild(emptyMsg);
            }

            const row = document.createElement('div');
            row.className = 'row g-2 align-items-end border rounded p-2 mb-2 sidebar-tag-menu-item-row';
            row.setAttribute('data-item-id', `bm-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`);
            row.innerHTML = `
                <div class="col-md-4">
                    <label class="form-label small mb-1">Title</label>
                    <input type="text" class="form-control form-control-sm sidebar-tag-menu-title" placeholder="Menu label">
                </div>
                <div class="col-md-6">
                    <label class="form-label small mb-1">Scene</label>
                    <select class="form-select form-select-sm sidebar-tag-menu-node">${nodeOptionsHtml}</select>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger sidebar-tag-menu-remove-btn" title="Remove">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            `;
            list.appendChild(row);
            persistMenuItemsJson(slotEl);
        });

        list.addEventListener('click', (event) => {
            const btn = event.target.closest('.sidebar-tag-menu-remove-btn');
            if (!btn) {
                return;
            }
            btn.closest('.sidebar-tag-menu-item-row')?.remove();
            if (list.querySelectorAll('.sidebar-tag-menu-item-row').length === 0) {
                let emptyMsg = list.querySelector('.sidebar-tag-menu-empty');
                if (!emptyMsg) {
                    emptyMsg = document.createElement('p');
                    emptyMsg.className = 'text-muted small sidebar-tag-menu-empty mb-0';
                    emptyMsg.textContent = 'No menu items yet. Click "Add menu item".';
                    list.appendChild(emptyMsg);
                } else {
                    emptyMsg.classList.remove('d-none');
                }
            }
            persistMenuItemsJson(slotEl);
        });

        list.addEventListener('input', () => persistMenuItemsJson(slotEl));
        list.addEventListener('change', () => persistMenuItemsJson(slotEl));

        persistMenuItemsJson(slotEl);
    };

    const initInfoModalCharCounters = (wrap) => {
        wrap.querySelectorAll('.sidebar-info-modal-title-input').forEach((input) => {
            const counter = input.parentElement?.querySelector('.sidebar-info-modal-char-count');
            if (!counter) {
                return;
            }
            const max = Number(counter.getAttribute('data-max')) || 60;
            const sync = () => {
                counter.textContent = `${input.value.length}/${max} characters`;
            };
            input.addEventListener('input', sync);
            sync();
        });
    };

    const initInfoModalFields = (wrap) => {
        if (!wrap || wrap.dataset.infoModalInit === '1') {
            return;
        }
        wrap.dataset.infoModalInit = '1';

        const sectionTabs = wrap.querySelectorAll('.sidebar-info-modal-section-tabs .nav-link');
        const sectionPanes = wrap.querySelectorAll('.sidebar-info-modal-section-pane');
        const customCb = wrap.querySelector('.sidebar-info-modal-use-custom');
        const iframeCb = wrap.querySelector('.sidebar-info-modal-iframe-toggle');
        const iframeWrap = wrap.querySelector('.sidebar-info-modal-iframe-wrap');
        const editorWrap = wrap.querySelector('.sidebar-info-modal-editor-wrap');

        const syncIframe = () => {
            const customOn = !!customCb?.checked;
            const iframeOn = !!iframeCb?.checked && !customOn;
            iframeWrap?.classList.toggle('d-none', !iframeOn);
            editorWrap?.classList.toggle('d-none', iframeOn);
        };

        const syncCustomModal = () => {
            const on = !!customCb?.checked;
            wrap.querySelectorAll('.sidebar-info-modal-title-standard').forEach((el) => {
                el.classList.toggle('d-none', on);
            });
            wrap.querySelectorAll('.sidebar-info-modal-title-custom').forEach((el) => {
                el.classList.toggle('d-none', !on);
            });
            wrap.querySelectorAll('.sidebar-info-modal-footer-fields').forEach((el) => {
                el.classList.toggle('d-none', on);
            });
            wrap.querySelectorAll('.sidebar-info-modal-content-standard').forEach((el) => {
                el.classList.toggle('d-none', on);
            });
            syncIframe();
        };

        const showSection = (section) => {
            sectionTabs.forEach((tab) => {
                tab.classList.toggle('active', tab.getAttribute('data-section') === section);
            });
            sectionPanes.forEach((pane) => {
                pane.classList.toggle('d-none', pane.getAttribute('data-section') !== section);
            });
            if (section === 'content' || section === 'footer') {
                reinitalizeEditors();
            }
        };

        sectionTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                showSection(tab.getAttribute('data-section') || 'title');
            });
        });

        customCb?.addEventListener('change', syncCustomModal);
        iframeCb?.addEventListener('change', syncIframe);

        syncCustomModal();
        initInfoModalCharCounters(wrap);
    };

    const initInfoModalEditorsForSlot = (slotEl) => {
        const action = slotEl.querySelector('input.sidebar-tag-action-radio:checked')?.value;
        if (action !== 'openInfoModal') {
            return;
        }
        slotEl.querySelectorAll('.sidebar-info-modal-fields').forEach((wrap) => {
            initInfoModalFields(wrap);
        });
        reinitalizeEditors();
    };

    form.querySelectorAll('.sidebar-config-tag-slot').forEach((slotEl) => {
        syncTagSlot(slotEl);
        initMenuItemsEditor(slotEl);
        initMediaActionFields(slotEl);
        initNavigateNodeFields(slotEl);
        initInfoModalEditorsForSlot(slotEl);

        slotEl.querySelectorAll('.sidebar-tag-action-radio').forEach((radio) => {
            radio.addEventListener('change', () => {
                if (radio.checked && radio.value === 'openInfoModal') {
                    initInfoModalEditorsForSlot(slotEl);
                }
                if (radio.checked && radio.value === 'navigateToNode') {
                    initNavigateNodeFields(slotEl);
                }
            });
        });
    });

    form.addEventListener('submit', () => {
        form.querySelectorAll('.sidebar-config-tag-slot').forEach((slotEl) => {
            const prefix = slotEl.getAttribute('data-tag-prefix');
            if (!prefix) {
                return;
            }

            persistMenuItemsJson(slotEl);

            const imagesHidden = slotEl.querySelector(`#${prefix}_existing_images_json`);
            const imagesPreview = slotEl.querySelector(`#${prefix}_images_preview`);
            if (!imagesHidden || !imagesPreview) {
                return;
            }
            const payload = [];
            imagesPreview.querySelectorAll('.sidebar-tag-image-item').forEach((item) => {
                const url = item.getAttribute('data-url');
                const fileName = item.getAttribute('data-file-name') || '';
                if (url) {
                    payload.push({ url, fileName });
                }
            });
            imagesHidden.value = JSON.stringify(payload);
        });
    });

    if (typeof syncTourLanguageTabs === 'function') {
        syncTourLanguageTabs(form);
    }
});
