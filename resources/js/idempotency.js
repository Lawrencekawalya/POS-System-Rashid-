/**
 * Duplicate-submission protection for every form that changes data.
 *
 * 1. Each POST form gets a one-time key (see App\Http\Middleware\PreventDuplicateSubmissions).
 *    If the same form is sent twice with the same data - a double click, a retry on a slow
 *    connection - the server processes it once and answers both requests the same way.
 * 2. While a form is being sent, its submit buttons are disabled so it is hard to send twice.
 */

const FIELD = '_idempotency_key';
const RELEASE_AFTER_MS = 15000;

function newKey() {
    if (window.crypto?.randomUUID) {
        return window.crypto.randomUUID();
    }

    const bytes = window.crypto.getRandomValues(new Uint8Array(16));

    return Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
}

const changesData = (form) => form instanceof HTMLFormElement && form.method === 'post';

// "formdata" fires for normal submits and for form.submit(), so every POST form is covered.
document.addEventListener(
    'formdata',
    (event) => {
        const form = event.target;

        if (!changesData(form) || event.formData.has(FIELD)) {
            return;
        }

        form.dataset.idempotencyKey ||= newKey();
        event.formData.append(FIELD, form.dataset.idempotencyKey);
    },
    true,
);

function release(button) {
    button.disabled = false;
    button.removeAttribute('data-submitting');
    button.removeAttribute('aria-busy');
}

document.addEventListener('submit', (event) => {
    const form = event.target;

    // Cancelled by a confirm() or handled by script: nothing is being sent.
    if (event.defaultPrevented || !changesData(form)) {
        return;
    }

    const buttons = Array.from(form.elements).filter((element) => element.type === 'submit' && !element.disabled);

    // Disable after the browser has read the clicked button's name and value.
    setTimeout(() => {
        buttons.forEach((button) => {
            button.disabled = true;
            button.setAttribute('data-submitting', '');
            button.setAttribute('aria-busy', 'true');
        });
    }, 0);

    // Downloads and new tabs do not leave the page, so give the buttons back.
    setTimeout(() => buttons.forEach(release), RELEASE_AFTER_MS);
});

// Coming back with the Back button shows the old page from memory: treat its forms as new.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('form[data-idempotency-key]').forEach((form) => delete form.dataset.idempotencyKey);
    document.querySelectorAll('[data-submitting]').forEach(release);
});
