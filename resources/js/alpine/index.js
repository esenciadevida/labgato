import { liveSearch } from './live-search';
import { liveResults } from './live-results';

const TOAST_DEFAULT_TITLES = {
    success: 'Success',
    error: 'Error',
    warning: 'Warning',
    info: 'Information',
};

window.Alpine.store('toast', {
    toasts: [],
    nextId: 0,

    push(type, message, title = null, options = {}) {
        const id = ++this.nextId;
        const duration = options.duration || (type === 'error' ? 7000 : 4500);

        this.toasts.push({
            id,
            type,
            message,
            title: title || TOAST_DEFAULT_TITLES[type] || 'Notification',
            visible: true,
        });

        setTimeout(() => this.dismiss(id), duration);

        return id;
    },

    dismiss(id) {
        const toast = this.toasts.find((t) => t.id === id);
        if (toast) {
            toast.visible = false;
        }

        setTimeout(() => {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        }, 250);
    },
});

window.LabgotoToast = {
    success(message, title) {
        window.Alpine.store('toast').push('success', message, title);
    },
    error(message, title) {
        window.Alpine.store('toast').push('error', message, title);
    },
    warning(message, title) {
        window.Alpine.store('toast').push('warning', message, title);
    },
    info(message, title) {
        window.Alpine.store('toast').push('info', message, title);
    },
};

window.Alpine.store('confirm', {
    open: false,
    message: '',
    title: 'Please confirm',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    danger: true,
    _resolver: null,

    show(message, options = {}) {
        this.message = message;
        this.title = options.title || 'Please confirm';
        this.confirmText = options.confirmText || 'Confirm';
        this.cancelText = options.cancelText || 'Cancel';
        this.danger = options.danger !== false;
        this.open = true;

        return new Promise((resolve) => {
            this._resolver = resolve;
        });
    },

    proceed() {
        this.open = false;
        if (this._resolver) {
            this._resolver(true);
            this._resolver = null;
        }
    },

    cancel() {
        this.open = false;
        if (this._resolver) {
            this._resolver(false);
            this._resolver = null;
        }
    },
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form');

    if (!form || !form.hasAttribute('data-confirm') || form.dataset.confirmRequested === '1') {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    const message = form.getAttribute('data-confirm');

    window.Alpine.store('confirm').show(message).then((confirmed) => {
        if (confirmed) {
            form.dataset.confirmRequested = '1';
            form.submit();
        }
    });
}, true);

document.addEventListener('alpine:init', () => {
    window.Alpine.data('liveSearch', (liveUrl, searchUrl) => liveSearch(liveUrl, searchUrl));

    window.Alpine.data('liveResults', (config) => liveResults(config));

    window.Alpine.data('popup', (popupId, triggerType, delaySeconds) => ({
        open: false,
        storageKey: 'labgato-dismissed-popup-' + popupId,
        init() {
            if (localStorage.getItem(this.storageKey)) {
                return;
            }

            if (triggerType === 'after_delay') {
                this.delayTimer = setTimeout(() => {
                    this.open = true;
                }, (delaySeconds || 0) * 1000);
            } else {
                this.open = true;
            }
        },
        dismiss() {
            this.open = false;
            localStorage.setItem(this.storageKey, '1');
        },
        onKeydownTab(event) {
            if (!this.open) {
                return;
            }

            const focusables = this.$root.querySelectorAll('a[href], button:not([disabled])');
            if (focusables.length === 0) {
                return;
            }

            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }));

    window.Alpine.data('announcementBanner', (announcementId) => ({
        dismissed: false,
        storageKey: 'labgato-dismissed-announcement-' + announcementId,
        init() {
            this.dismissed = !!localStorage.getItem(this.storageKey);
        },
        dismiss() {
            this.dismissed = true;
            localStorage.setItem(this.storageKey, '1');
        },
    }));
});
