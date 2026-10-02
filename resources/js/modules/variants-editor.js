/**
 * Редактор вариантов товара в кабинете производителя:
 * добавление и удаление строк «название — цена — наличие».
 */
export function initVariantsEditor() {
    const editor = document.querySelector('[data-variants-editor]');
    if (!editor) return;

    const rows = editor.querySelector('[data-variants-rows]');
    const template = editor.querySelector('template');
    const emptyHint = editor.querySelector('[data-variants-empty]');
    let index = rows.children.length;

    const refresh = () => emptyHint?.classList.toggle('d-none', rows.children.length > 0);

    editor.querySelector('[data-variant-add]').addEventListener('click', () => {
        const html = template.innerHTML.replaceAll('__INDEX__', index++);
        rows.insertAdjacentHTML('beforeend', html);
        rows.lastElementChild.querySelector('input')?.focus();
        refresh();
    });

    rows.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-variant-remove]');
        if (!remove) return;
        remove.closest('[data-variant-row]').remove();
        refresh();
    });

    refresh();
}
