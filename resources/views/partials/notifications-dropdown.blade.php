{{-- Колокольчик уведомлений с выпадающим списком последних уведомлений --}}
<div class="dropdown">
    <button class="header-icon" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Уведомления">
        <i class="bi bi-bell"></i>
        <span class="counter-badge counter-badge-danger" data-count="{{ $nav['notifications'] }}">{{ $nav['notifications'] ?: '' }}</span>
    </button>
    <div class="dropdown-menu dropdown-menu-end notifications-menu">
        <div class="notifications-menu__head">
            <span class="fw-bold">Уведомления</span>
            @if ($nav['notifications'])
                <form method="POST" action="{{ route('notifications.read-all') }}" data-no-loading>
                    @csrf
                    <button class="btn btn-link btn-sm p-0 text-decoration-none">Прочитать все</button>
                </form>
            @endif
        </div>
        @forelse ($nav['latestNotifications'] as $notification)
            <a href="{{ route('notifications.open', $notification->id) }}" class="notification-item {{ $notification->read_at ? '' : 'is-unread' }}">
                <span class="notification-item__icon"><i class="bi {{ $notification->data['icon'] ?? 'bi-bell' }}"></i></span>
                <span class="min-w-0">
                    <span class="notification-item__title d-block">{{ $notification->data['title'] ?? 'Уведомление' }}</span>
                    <span class="notification-item__text d-block">{{ $notification->data['message'] ?? '' }}</span>
                    <span class="notification-item__time d-block">{{ $notification->created_at->diffForHumans() }}</span>
                </span>
            </a>
        @empty
            <div class="text-center text-secondary small py-4 px-3">
                <i class="bi bi-bell-slash fs-3 d-block mb-1 text-muted-2"></i>
                Новых уведомлений нет
            </div>
        @endforelse
        <div class="notifications-menu__foot">
            <a href="{{ route('notifications.index') }}">Все уведомления</a>
        </div>
    </div>
</div>
