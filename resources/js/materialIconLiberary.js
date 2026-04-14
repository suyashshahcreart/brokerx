import * as bootstrap from 'bootstrap';

export class IconLibrary {

    constructor({ materialIconList }) {
        this.icons = materialIconList;
        this.filteredIcons = [...materialIconList];
        this.targetInput = null;
        this.modal = null;
    }

    init(iconModalId, searchInputId, closeModalButtonId) {
        this.initCDNLinks()
        this.modal = new bootstrap.Modal(document.getElementById(iconModalId));
        this.searchInput = $(`#${searchInputId}`);
        this.searchInput.on('input', (e) => {
            this.search(e.target.value);
        });
        this.closeModalButton = $(`#${closeModalButtonId}`);
        this.closeModalButton.on('click', () => {
            this.searchInput.val('');
            this.filteredIcons = this.icons;
            this.modal.hide();
        });
    }

    open(inputSelector, previewIcon) {
        this.targetInput = inputSelector;
        this.previewIcon = previewIcon ? $(previewIcon) : $(inputSelector).closest('.input-group').find('.icon-preview');
        this.renderIcons();
        this.modal.show();
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
        console.log('Rendering icons:', this.filteredIcons);
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
        $(this.targetInput).val(icon.value);
        this.searchInput.val('');
        if (this.previewIcon && this.previewIcon.length) {
            console.log('Selected icon:', icon);
            this.previewIcon.html(`
                <div class="icon-item text-center">
                    <span class="material-icons-outlined">${icon.value}</span>
                </div>
            `);
        }
        this.filteredIcons = this.icons;
        this.modal.hide();
    }

    initCDNLinks() {

        //setup the icon CDN in the DOM.
        const preconnectLink = document.createElement("link");
        preconnectLink.rel = "preconnect";
        preconnectLink.href = "https://fonts.googleapis.com";
        document.head.appendChild(preconnectLink);

        const preconnectLink2 = document.createElement("link");
        preconnectLink2.rel = "preconnect";
        preconnectLink2.href = "https://fonts.gstatic.com";
        preconnectLink2.crossOrigin = "anonymous";
        document.head.appendChild(preconnectLink2);

        const preloadLink = document.createElement("link");
        preloadLink.rel = "preload";
        preloadLink.href = "https://fonts.googleapis.com/icon?family=Material+Icons+Outlined";
        preloadLink.as = "style";
        preloadLink.crossOrigin = "anonymous";
        document.head.appendChild(preloadLink);

        const materialIconsLink = document.createElement("link");
        materialIconsLink.rel = "stylesheet";
        materialIconsLink.href = "https://fonts.googleapis.com/icon?family=Material+Icons+Outlined";
        materialIconsLink.crossOrigin = "anonymous";
        document.head.appendChild(materialIconsLink);
    }
}