(() => {
    'use strict';

    const getModal = (id) => {
        if (!id) return null;

        const cleanId = String(id).replace(/^#/, '');

        return document.getElementById(cleanId);
    };

    const openModal = (id) => {
        const modal = getModal(id);

        if (!modal) return;

        modal.classList.add('open');
        modal.removeAttribute('hidden');
        document.body.classList.add('hms-modal-open');

        const focusable = modal.querySelector(
            'input:not([type="hidden"]), select, textarea, button:not([data-modal-close])'
        );

        window.setTimeout(() => {
            focusable?.focus();
        }, 50);
    };

    const closeModal = (target) => {
        const modal = target instanceof HTMLElement
            ? target.closest('[data-hms-modal]')
            : getModal(target);

        if (!modal) return;

        modal.classList.remove('open');

        if (!document.querySelector('[data-hms-modal].open')) {
            document.body.classList.remove('hms-modal-open');
        }
    };

    window.HMSModal = {
        open: openModal,
        close: closeModal,
    };

    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-modal-open], [data-modal]');

        if (opener) {
            event.preventDefault();
            openModal(opener.dataset.modalOpen || opener.dataset.modal);
            return;
        }

        const closer = event.target.closest('[data-modal-close], .modal-close');

        if (closer) {
            event.preventDefault();
            closeModal(closer);
            return;
        }

        const modal = event.target.closest('[data-hms-modal]');

        if (
            modal
            && event.target === modal
            && modal.dataset.closeBackdrop !== '0'
        ) {
            closeModal(modal);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;

        const openModals = Array.from(
            document.querySelectorAll('[data-hms-modal].open')
        );

        closeModal(openModals.at(-1));
    });

    /*
    |--------------------------------------------------------------------------
    | Generic Edit Modal Population
    |--------------------------------------------------------------------------
    |
    | Add data-edit-record='{"name":"..."}' to an edit trigger and
    | data-edit-field="name" to the corresponding form field.
    |
    */

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-edit-record]');

        if (!trigger) return;

        let record = {};

        try {
            record = JSON.parse(
                trigger.dataset.editRecord || '{}'
            );
        } catch (_) {
            record = {};
        }

        const modal = getModal(
            trigger.dataset.modalOpen
        );

        if (!modal) return;

        modal.querySelectorAll('[data-edit-field]')
            .forEach((field) => {
                const key = field.dataset.editField;
                const value = record[key];

                if (field.type === 'checkbox') {
                    field.checked = Boolean(
                        Number(value) || value === true
                    );
                    return;
                }

                if (value !== undefined && value !== null) {
                    field.value = String(value);
                }
            });

        const form = modal.querySelector(
            'form[data-edit-form]'
        );

        if (
            form
            && trigger.dataset.editAction
        ) {
            form.action = trigger.dataset.editAction;
        }
    });
})();
