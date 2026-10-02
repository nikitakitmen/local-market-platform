/**
 * Поведение форм: подтверждение удаления, состояние загрузки кнопок,
 * показ пароля, предпросмотр изображений, автоотправка фильтров.
 */
import { Modal } from 'bootstrap';

/** Подтверждение опасных действий через модальное окно: <form data-confirm="Удалить товар?"> */
function initConfirm() {
    const modalEl = document.getElementById('confirmModal');
    if (!modalEl) return;

    const modal = Modal.getOrCreateInstance(modalEl);
    const messageEl = modalEl.querySelector('[data-confirm-message]');
    const submitBtn = modalEl.querySelector('[data-confirm-submit]');
    let pendingForm = null;

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        pendingForm = form;
        messageEl.textContent = form.dataset.confirm;
        submitBtn.textContent = form.dataset.confirmButton || 'Удалить';
        submitBtn.className = `btn ${form.dataset.confirmClass || 'btn-danger'}`;
        modal.show();
    }, true);

    submitBtn.addEventListener('click', () => {
        if (!pendingForm) return;
        pendingForm.dataset.confirmed = '1';
        modal.hide();
        pendingForm.requestSubmit();
    });
}

/** Кнопка отправки получает спиннер, чтобы пользователь видел, что запрос выполняется. */
function initLoadingButtons() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.noLoading !== undefined || event.defaultPrevented) return;

        const button = event.submitter || form.querySelector('[type="submit"]');
        if (!button || button.classList.contains('is-loading')) return;

        if (!button.querySelector('.btn-spinner')) {
            const spinner = document.createElement('span');
            spinner.className = 'btn-spinner';
            button.prepend(spinner);
        }
        // Кнопку блокируем чуть позже, чтобы её name/value успели попасть в запрос
        setTimeout(() => button.classList.add('is-loading'), 0);
    });

    // Возврат на страницу кнопкой «назад» — снимаем состояние загрузки
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('.btn.is-loading').forEach((btn) => btn.classList.remove('is-loading'));
    });
}

function initPasswordToggle() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('input');
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.innerHTML = `<i class="bi ${visible ? 'bi-eye' : 'bi-eye-slash'}"></i>`;
        });
    });
}

/** Предпросмотр выбранного изображения: <input type="file" data-preview="#logoPreview"> */
function initImagePreview() {
    document.querySelectorAll('input[type="file"][data-preview]').forEach((input) => {
        input.addEventListener('change', () => {
            const target = document.querySelector(input.dataset.preview);
            const file = input.files?.[0];
            if (!target || !file || !file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                target.style.backgroundImage = `url('${e.target.result}')`;
                target.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        });
    });
}

/** Автоматическая отправка формы при изменении поля: <select data-autosubmit> */
function initAutoSubmit() {
    document.querySelectorAll('[data-autosubmit]').forEach((field) => {
        field.addEventListener('change', () => field.form?.requestSubmit());
    });
}

/** Зависимый список подкатегорий: <select data-subcategory-source="#category"> */
function initDependentSelects() {
    document.querySelectorAll('select[data-subcategory-for]').forEach((sub) => {
        const parent = document.querySelector(sub.dataset.subcategoryFor);
        if (!parent) return;

        const options = Array.from(sub.querySelectorAll('option[data-parent]'));

        const update = (resetValue) => {
            const parentId = parent.value;
            options.forEach((option) => {
                const match = parentId && option.dataset.parent === parentId;
                option.hidden = !match;
                option.disabled = !match;
            });
            if (resetValue && sub.selectedOptions[0]?.disabled) {
                sub.value = '';
            }
            sub.disabled = !parentId || !options.some((o) => !o.disabled);
        };

        parent.addEventListener('change', () => update(true));
        update(false);
    });
}

/** Счётчик символов для textarea: <textarea data-counter maxlength="500"> */
function initCounters() {
    document.querySelectorAll('textarea[data-counter][maxlength]').forEach((area) => {
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        area.after(counter);
        const update = () => (counter.textContent = `${area.value.length} / ${area.maxLength}`);
        area.addEventListener('input', update);
        update();
    });
}

/** Кнопки быстрого входа под демо-аккаунтами на странице входа. */
function initDemoLogin() {
    document.querySelectorAll('[data-demo-login]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('email').value = button.dataset.demoLogin;
            document.getElementById('password').value = 'password';
            document.getElementById('password').form.requestSubmit();
        });
    });
}

export function initForms() {
    initConfirm();
    initLoadingButtons();
    initPasswordToggle();
    initImagePreview();
    initAutoSubmit();
    initDependentSelects();
    initCounters();
    initDemoLogin();
}
