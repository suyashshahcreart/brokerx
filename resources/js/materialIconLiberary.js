import * as bootstrap from 'bootstrap';

export class IconLibrary {

    constructor({ materialIconList }) {
        this.icons = materialIconList;
        this.filteredIcons = [...materialIconList];
        this.targetInput = null;
        this.previewIcon = null;
        this.previousFocusedElement = null;
        this.focusRestoreTarget = null;
        this.modal = null;
        this.modalElement = null;
        this.searchInput = null;
        this.closeModalButton = null;
        this.hiddenHandler = null;
    }

    init(iconModalId, searchInputId, closeModalButtonId) {
        this.initCDNLinks();
        this.modalElement = document.getElementById(iconModalId);
        if (!this.modalElement) return;

        this.modal = bootstrap.Modal.getOrCreateInstance(this.modalElement);
        this.searchInput = $(`#${searchInputId}`);
        this.searchInput.off('input.iconLibrary').on('input.iconLibrary', (e) => {
            this.search(e.target.value);
        });
        this.closeModalButton = $(`#${closeModalButtonId}`);
        this.closeModalButton.off('click.iconLibrary').on('click.iconLibrary', () => {
            this.hide();
        });

        if (this.hiddenHandler) {
            this.modalElement.removeEventListener('hidden.bs.modal', this.hiddenHandler);
        }
        this.hiddenHandler = () => {
            this.restoreFocus();
            this.resetState();
        };
        this.modalElement.addEventListener('hidden.bs.modal', this.hiddenHandler);
    }

    open(inputSelector, previewIcon) {
        this.targetInput = inputSelector;
        this.previewIcon = previewIcon;
        this.previousFocusedElement = document.activeElement;
        
        // Convert jQuery object to DOM element if needed
        if (inputSelector instanceof $ || (inputSelector && inputSelector.jquery)) {
            this.focusRestoreTarget = inputSelector[0]; // Get DOM element from jQuery
        } else if (typeof inputSelector === 'string') {
            this.focusRestoreTarget = document.querySelector(inputSelector);
        } else {
            this.focusRestoreTarget = inputSelector;
        }
        
        this.renderIcons();
        this.modal.show();
    }

    hide() {
        this.modal?.hide();
    }

    /**
     * @param query string
     * @returns icon[]     
     * search the icon base on the label 
     * */
    search(query) {
        this.filteredIcons = this.icons.filter(icon =>
            icon.label.toLowerCase().includes(query.toLowerCase())
        );
        this.renderIcons();
    }

    /* Renser the icon in the FilteredIcon Array */
    renderIcons() {
        const container = $('#iconContainer');
        container.empty();
        this.filteredIcons.forEach(icon => {
            const el = $(`
                <div class="icon-item text-center">
                    <span class="material-icons-outlined">${icon.value}</span>
                </div>
            `);

            el.on('click', () => this.selectIcon(icon));
            container.append(el);
        });
    }
    /* Set the icon on the target input */
    selectIcon(icon) {
        // Handle both jQuery objects and string selectors
        const targetInput = this.targetInput instanceof $ || (this.targetInput && this.targetInput.jquery) 
            ? this.targetInput 
            : $(this.targetInput);
        targetInput.val(icon.value);
        
        this.searchInput.val('');
        if (this.previewIcon && this.previewIcon.length) {
            this.previewIcon.html(`
                <div class="icon-item text-center">
                    <span class="material-icons-outlined">${icon.value}</span>
                </div>
            `);
        }
        this.hide();
    }

    resetState() {
        this.searchInput?.val('');
        this.filteredIcons = [...this.icons];
        this.targetInput = null;
        this.previewIcon = null;
    }

    restoreFocus() {
        const focusTarget = this.previousFocusedElement;
        this.previousFocusedElement = null;

        if (focusTarget && typeof focusTarget.focus === 'function' && document.contains(focusTarget)) {
            focusTarget.focus();
            this.focusRestoreTarget = null;
            return;
        }

        // Handle jQuery objects and DOM elements
        let fallbackTarget = this.focusRestoreTarget;
        if (fallbackTarget instanceof $ || (fallbackTarget && fallbackTarget.jquery)) {
            fallbackTarget = fallbackTarget[0]; // Get the DOM element from jQuery object
        }
        
        if (fallbackTarget && typeof fallbackTarget.focus === 'function') {
            fallbackTarget.focus();
        }
        this.focusRestoreTarget = null;
    }

    initCDNLinks() {

        //setup the icon CDN in the DOM.
        const ensureLink = (selector, createLink) => {
            if (!document.head.querySelector(selector)) {
                document.head.appendChild(createLink());
            }
        };

        ensureLink('link[href="https://fonts.googleapis.com"]', () => {
            const preconnectLink = document.createElement("link");
            preconnectLink.rel = "preconnect";
            preconnectLink.href = "https://fonts.googleapis.com";
            return preconnectLink;
        });

        ensureLink('link[href="https://fonts.gstatic.com"]', () => {
            const preconnectLink2 = document.createElement("link");
            preconnectLink2.rel = "preconnect";
            preconnectLink2.href = "https://fonts.gstatic.com";
            preconnectLink2.crossOrigin = "anonymous";
            return preconnectLink2;
        });

        ensureLink('link[href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined"]', () => {
            const preloadLink = document.createElement("link");
            preloadLink.rel = "preload";
            preloadLink.href = "https://fonts.googleapis.com/icon?family=Material+Icons+Outlined";
            preloadLink.as = "style";
            preloadLink.crossOrigin = "anonymous";
            return preloadLink;
        });

        ensureLink('link[rel="stylesheet"][href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined"]', () => {
            const materialIconsLink = document.createElement("link");
            materialIconsLink.rel = "stylesheet";
            materialIconsLink.href = "https://fonts.googleapis.com/icon?family=Material+Icons+Outlined";
            materialIconsLink.crossOrigin = "anonymous";
            return materialIconsLink;
        });
    }
}