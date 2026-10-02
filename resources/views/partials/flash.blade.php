{{-- Контейнер всплывающих уведомлений и флеш-сообщения из сессии --}}
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer" aria-live="polite"></div>

@foreach (['success' => 'success', 'error' => 'error', 'info' => 'info'] as $key => $type)
    @if (session($key))
        <div data-flash-toast data-type="{{ $type }}" data-message="{{ session($key) }}" hidden></div>
    @endif
@endforeach

@if ($errors->any())
    <div data-flash-toast data-type="error" data-message="{{ $errors->first() }}" hidden></div>
@endif
