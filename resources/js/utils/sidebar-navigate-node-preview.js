import $ from 'jquery';
import { Viewer } from '@photo-sphere-viewer/core';
import '@photo-sphere-viewer/core/index.css';
import 'select2';
import 'select2/dist/css/select2.min.css';

const tourNodesById = () => {
    const nodes = Array.isArray(window.sidebarConfigTourNodes) ? window.sidebarConfigTourNodes : [];
    const map = new Map();
    nodes.forEach((node) => {
        if (node?.id != null) {
            map.set(String(node.id), node);
        }
    });
    return map;
};

const parseNodeView = (raw) => {
    if (!raw || raw === 'null') {
        return null;
    }
    try {
        const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
        if (parsed && typeof parsed.yaw === 'number' && !Number.isNaN(parsed.yaw)) {
            return parsed;
        }
    } catch {
        return null;
    }
    return null;
};

const applyNodeViewToViewer = (viewer, nodeView) => {
    if (!viewer || !nodeView) {
        return;
    }
    if (typeof nodeView.yaw === 'number' && !Number.isNaN(nodeView.yaw)) {
        viewer.rotate({ yaw: nodeView.yaw, pitch: 0 });
    }
    if (typeof nodeView.zoom === 'number' && !Number.isNaN(nodeView.zoom) && typeof viewer.zoom === 'function') {
        viewer.zoom(nodeView.zoom);
    } else if (typeof nodeView.fov === 'number' && !Number.isNaN(nodeView.fov) && typeof viewer.setFov === 'function') {
        viewer.setFov(nodeView.fov);
    } else if (
        typeof nodeView.depth === 'number'
        && !Number.isNaN(nodeView.depth)
        && typeof viewer.setFov === 'function'
    ) {
        const maxFov = viewer.config?.maxFov ?? 90;
        const minFov = viewer.config?.minFov ?? 30;
        viewer.setFov(maxFov - nodeView.depth * (maxFov - minFov));
    }
};

const syncNavigateNodeUi = (wrap) => {
    const nodeId = wrap.querySelector('.sidebar-tag-node-select')?.value?.trim() || '';
    const viewJson = wrap.querySelector('.sidebar-tag-node-view-json')?.value || '';
    const hasView = !!parseNodeView(viewJson);
    const selectLabel = wrap.querySelector('.sidebar-tag-node-select-label');
    const previewLabel = wrap.querySelector('.sidebar-tag-node-preview-label');
    const viewHint = wrap.querySelector('.sidebar-tag-node-view-hint');
    const viewSet = wrap.querySelector('.sidebar-tag-node-view-set');
    const previewWrapper = wrap.querySelector('.sidebar-tag-node-preview-wrapper');

    selectLabel?.classList.toggle('text-danger', nodeId === '');
    previewLabel?.classList.toggle('text-danger', nodeId !== '' && !hasView);
    previewWrapper?.classList.toggle('border-danger', nodeId !== '' && !hasView);
    viewHint?.classList.toggle('d-none', !(nodeId !== '' && !hasView));
    viewSet?.classList.toggle('d-none', !hasView);
};

const destroyViewer = (state) => {
    if (state.viewer) {
        try {
            state.viewer.destroy();
        } catch {
            /* ignore */
        }
        state.viewer = null;
    }
    state.previewReady = false;
};

const initNavigateNodePreview = (wrap) => {
    if (!wrap || wrap.dataset.navigateInit === '1') {
        return wrap?._navigateState || null;
    }
    wrap.dataset.navigateInit = '1';

    const state = {
        viewer: null,
        previewReady: false,
        currentNodeId: null,
    };
    wrap._navigateState = state;

    const nodeSelect = wrap.querySelector('.sidebar-tag-node-select');
    const viewHidden = wrap.querySelector('.sidebar-tag-node-view-json');
    const canvas = wrap.querySelector('.sidebar-tag-node-preview-canvas');
    const setViewBtn = wrap.querySelector('.sidebar-tag-node-set-view-btn');
    const loadingEl = wrap.querySelector('.sidebar-tag-node-preview-loading');
    const emptyEl = wrap.querySelector('.sidebar-tag-node-preview-empty');
    const unavailableEl = wrap.querySelector('.sidebar-tag-node-preview-unavailable');
    const nodesMap = tourNodesById();

    const setLoading = (on) => {
        loadingEl?.classList.toggle('d-none', !on);
    };

    const updateSetViewButton = () => {
        if (!setViewBtn) {
            return;
        }
        const hasView = !!parseNodeView(viewHidden?.value);
        setViewBtn.style.display = state.currentNodeId ? '' : 'none';
        setViewBtn.disabled = !state.previewReady;
        setViewBtn.classList.toggle('btn-warning', hasView);
        setViewBtn.classList.toggle('text-dark', hasView);
        setViewBtn.classList.toggle('btn-dark', !hasView);
        setViewBtn.innerHTML = hasView
            ? '<i class="ri-refresh-line me-1"></i> Reset View'
            : '<i class="ri-eye-line me-1"></i> Set View';
    };

    const persistNodeView = (view) => {
        if (!viewHidden) {
            return;
        }
        viewHidden.value = view ? JSON.stringify(view) : '';
        syncNavigateNodeUi(wrap);
        updateSetViewButton();
    };

    const loadPreview = (nodeId) => {
        state.currentNodeId = nodeId || null;
        destroyViewer(state);
        updateSetViewButton();

        if (!nodeId) {
            emptyEl?.classList.remove('d-none');
            unavailableEl?.classList.add('d-none');
            setLoading(false);
            return;
        }

        emptyEl?.classList.add('d-none');
        const node = nodesMap.get(String(nodeId));
        const panoramaUrl = node?.panoramaUrl || null;

        if (!canvas || !panoramaUrl) {
            unavailableEl?.classList.remove('d-none');
            setLoading(false);
            return;
        }

        unavailableEl?.classList.add('d-none');
        setLoading(true);
        canvas.innerHTML = '';

        const previewViewer = new Viewer({
            container: canvas,
            panorama: panoramaUrl,
            navbar: false,
            touchmoveTwoFingers: false,
            defaultZoomLvl: 0,
            moveSpeed: 3,
            moveInertia: 0,
        });

        state.viewer = previewViewer;

        const handlePositionUpdated = (event) => {
            if (event?.position && typeof event.position.pitch === 'number' && event.position.pitch !== 0) {
                previewViewer.rotate({
                    yaw: event.position.yaw,
                    pitch: 0,
                });
            }
        };

        previewViewer.addEventListener('ready', () => {
            state.previewReady = true;
            setLoading(false);
            if (previewViewer?.dynamics?.position?.pitch) {
                const pitchDynamic = previewViewer.dynamics.position.pitch;
                pitchDynamic.min = 0;
                pitchDynamic.max = 0;
                if (pitchDynamic.current !== 0) {
                    pitchDynamic.setValue(0);
                }
            }
            previewViewer.addEventListener('position-updated', handlePositionUpdated);
            applyNodeViewToViewer(previewViewer, parseNodeView(viewHidden?.value));
            updateSetViewButton();
        }, { once: true });
    };

    if (nodeSelect && typeof $.fn.select2 === 'function') {
        const $select = $(nodeSelect);
        if (!$select.hasClass('select2-hidden-accessible')) {
            $select.select2({
                width: '100%',
                placeholder: 'Select a scene',
                allowClear: true,
                dropdownParent: $select.closest('.sidebar-config-tag-slot'),
            });
        }

        $select.on('change.sidebarNavigateNode', () => {
            const nextId = $select.val() ? String($select.val()) : '';
            persistNodeView(null);
            loadPreview(nextId);
            syncNavigateNodeUi(wrap);
        });
    } else if (nodeSelect) {
        nodeSelect.addEventListener('change', () => {
            persistNodeView(null);
            loadPreview(nodeSelect.value?.trim() || '');
            syncNavigateNodeUi(wrap);
        });
    }

    setViewBtn?.addEventListener('click', () => {
        const existing = parseNodeView(viewHidden?.value);
        if (existing) {
            persistNodeView(null);
            if (state.viewer && state.previewReady) {
                state.viewer.rotate({ yaw: 0, pitch: 0 });
                if (typeof state.viewer.zoom === 'function') {
                    state.viewer.zoom(0);
                }
            }
            return;
        }

        const viewer = state.viewer;
        if (!viewer || !state.previewReady) {
            return;
        }

        const pos = viewer.getPosition?.();
        const zoom = typeof viewer.getZoomLevel === 'function' ? viewer.getZoomLevel() : 0;
        let fov;
        if (typeof viewer.getFov === 'function') {
            fov = viewer.getFov();
        }

        persistNodeView({
            yaw: pos?.yaw ?? 0,
            pitch: 0,
            zoom,
            ...(typeof fov === 'number' && !Number.isNaN(fov) ? { fov } : {}),
        });
    });

    const initialNodeId = nodeSelect?.value?.trim() || '';
    if (initialNodeId) {
        loadPreview(initialNodeId);
    }
    syncNavigateNodeUi(wrap);
    updateSetViewButton();

    return state;
};

export const initNavigateNodeFields = (rootEl) => {
    if (!rootEl) {
        return;
    }
    rootEl.querySelectorAll('.sidebar-tag-navigate-node-fields').forEach((wrap) => {
        const section = wrap.closest('[data-action="navigateToNode"]');
        if (section?.classList.contains('d-none')) {
            return;
        }
        initNavigateNodePreview(wrap);
    });
};

export const refreshNavigateNodePreview = (wrap) => {
    if (!wrap?._navigateState) {
        initNavigateNodePreview(wrap);
        return;
    }
    const nodeSelect = wrap.querySelector('.sidebar-tag-node-select');
    const nodeId = nodeSelect?.value?.trim() || '';
    wrap._navigateState.currentNodeId = null;
    const canvas = wrap.querySelector('.sidebar-tag-node-preview-canvas');
    if (canvas) {
        canvas.innerHTML = '';
    }
    destroyViewer(wrap._navigateState);
    if (nodeId) {
        initNavigateNodePreview(wrap);
        wrap.dataset.navigateInit = '1';
        const select = wrap.querySelector('.sidebar-tag-node-select');
        if (select) {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
};

export default initNavigateNodeFields;
