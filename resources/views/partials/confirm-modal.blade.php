{{-- Модальное окно подтверждения: используется формами с атрибутом data-confirm --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px">
        <div class="modal-content">
            <div class="modal-body p-4 text-center">
                <div class="modal-icon modal-icon-danger mx-auto"><i class="bi bi-exclamation-triangle"></i></div>
                <h5 class="mb-2" id="confirmModalTitle">Подтвердите действие</h5>
                <p class="text-secondary mb-0" data-confirm-message>Вы уверены?</p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex gap-2 flex-nowrap">
                <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger flex-fill" data-confirm-submit>Удалить</button>
            </div>
        </div>
    </div>
</div>
