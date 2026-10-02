@extends(auth()->user()->panelLayout())

@section('title', 'Уведомления')

@section('page')
    <div class="dash-page-head">
        <div>
            <h1>Уведомления</h1>
            <p>Статусы заказов, отзывы, заявки и другие события.</p>
        </div>
        @if (auth()->user()->unreadNotifications()->exists())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-light"><i class="bi bi-check2-all"></i> Прочитать все</button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <x-empty-state icon="bell" title="Уведомлений нет" text="Здесь появятся сообщения о заказах и других событиях." />
    @else
        <div class="card overflow-hidden">
            @foreach ($notifications as $notification)
                <a href="{{ route('notifications.open', $notification->id) }}" class="notification-item {{ $notification->read_at ? '' : 'is-unread' }}">
                    <span class="notification-item__icon"><i class="bi {{ $notification->data['icon'] ?? 'bi-bell' }}"></i></span>
                    <span class="flex-grow-1 min-w-0">
                        <span class="notification-item__title d-block">{{ $notification->data['title'] ?? 'Уведомление' }}</span>
                        <span class="notification-item__text d-block">{{ $notification->data['message'] ?? '' }}</span>
                    </span>
                    <span class="notification-item__time text-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
@endsection
