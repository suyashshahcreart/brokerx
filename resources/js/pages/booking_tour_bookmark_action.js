document.addEventListener('DOMContentLoaded', () => {
    const actionSelect = document.getElementById('bookmark_action');

    if (!actionSelect) {
        return;
    }

    const sectionMap = {
        openInfoModal: 'bookmark_action_openInfoModal',
        openLink: 'bookmark_action_openLink',
        openDocument: 'bookmark_action_openDocument',
        openVideo: 'bookmark_action_openVideo',
        openImage: 'bookmark_action_openImage'
    };

    const allSections = document.querySelectorAll('.bookmark-action-section');

    const toggleBookmarkActionSections = () => {
        allSections.forEach((section) => {
            section.classList.add('d-none');
        });

        const selectedAction = actionSelect.value;
        const sectionId = sectionMap[selectedAction] || null;

        if (!sectionId) {
            return;
        }

        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.remove('d-none');
        }
    };

    actionSelect.addEventListener('change', toggleBookmarkActionSections);
    toggleBookmarkActionSections();
});
