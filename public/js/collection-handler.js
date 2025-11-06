document.addEventListener('DOMContentLoaded', () => {
    const initCollection = (id) => {
        const collection = document.getElementById(id);
        if (!collection) return;

        const baseClass = id.replace('-collection', '');
        const itemsContainer = collection.querySelector(`.${baseClass}-items`);
        const prototypeTemplate = collection.dataset.prototype;
        const prototypeName = collection.dataset.prototypeName;
        const allowAdd = collection.dataset.allowAdd === '1';
        const allowRemove = collection.dataset.allowRemove === '1';

        if (!itemsContainer || !prototypeTemplate) return;

        // Add new item
        const addBtn = collection.querySelector('.add-item');
        if (allowAdd && addBtn) {
            addBtn.addEventListener('click', () => {
                const index = itemsContainer.children.length;
                const newForm = prototypeTemplate.replaceAll(prototypeName, index);
                const wrapper = document.createElement('div');
                wrapper.classList.add('collection-item', 'mb-2', 'p-2', 'border', 'rounded', 'bg-light');
                wrapper.innerHTML = `
                    <div class="row g-2 align-items-center">
                        <div class="col-md-11">${newForm}</div>
                        <div class="col-md-1 text-end">
                            ${allowRemove ? '<button type="button" class="btn btn-outline-danger btn-sm remove-item" title="Remove item">&times;</button>' : ''}
                        </div>
                    </div>
                `;

                if (allowRemove) {
                    wrapper.querySelector('.remove-item').addEventListener('click', () => wrapper.remove());
                }

                itemsContainer.appendChild(wrapper);
            });
        }

        // Enable remove buttons on existing items
        if (allowRemove) {
            itemsContainer.querySelectorAll('.remove-item').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.currentTarget.closest('.collection-item')?.remove();
                });
            });
        }
    };

    // Initialize all dynamic collections
    [
        'require-collection',
        'blacklist-collection',
        'abandoned-collection',
        'minimum-stability-collection',
        'repositories-collection',
        'archive-whitelist-collection',
        'archive-blacklist-collection'
    ].forEach(initCollection);
});
