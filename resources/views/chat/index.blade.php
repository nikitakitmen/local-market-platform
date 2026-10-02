{{-- Чат: покупатель видит диалоги с производителями, производитель — с покупателями --}}
@extends($asProducer ? 'layouts.producer' : 'layouts.account')

@section('title', 'Сообщения')

@section('page')
    @php($routeName = $asProducer ? 'producer.messages' : 'account.messages')

    <div class="dash-page-head">
        <div>
            <h1>Сообщения</h1>
            <p>{{ $asProducer ? 'Вопросы покупателей о ваших товарах и заказах.' : 'Переписка с производителями.' }}</p>
        </div>
    </div>

    <div class="chat-layout {{ $conversation ? 'has-active' : '' }}">
        <div class="chat-list">
            @forelse ($conversations as $item)
                @php($title = $asProducer ? $item->buyer->name : $item->producer->name)
                <a href="{{ route($routeName, $item) }}" class="chat-list__item {{ $conversation?->id === $item->id ? 'active' : '' }}">
                    @if ($asProducer)
                        <span class="avatar">{{ $item->buyer->initials() }}</span>
                    @else
                        <x-producer-logo :producer="$item->producer" size="sm" />
                    @endif
                    <span class="flex-grow-1 min-w-0">
                        <span class="d-flex justify-content-between gap-2">
                            <span class="chat-list__name">{{ $title }}</span>
                            <span class="small text-secondary text-nowrap">{{ $item->latestMessage?->created_at->format('d.m') }}</span>
                        </span>
                        <span class="d-flex justify-content-between gap-2">
                            <span class="chat-list__preview">{{ $item->latestMessage?->body ?? 'Нет сообщений' }}</span>
                            @if ($item->unread_count)
                                <span class="badge rounded-pill text-bg-primary">{{ $item->unread_count }}</span>
                            @endif
                        </span>
                    </span>
                </a>
            @empty
                <x-empty-state icon="chat-dots" title="Диалогов пока нет"
                               :text="$asProducer ? 'Когда покупатели напишут вам, диалоги появятся здесь.' : 'Задайте вопрос производителю со страницы товара или магазина.'"
                               :compact="true" />
            @endforelse
        </div>

        <div class="chat-window">
            @if ($conversation)
                @php($lastId = $messages->last()?->id ?? 0)
                <div class="chat-window__head">
                    <a href="{{ route($routeName) }}" class="btn btn-light btn-icon btn-sm d-lg-none" aria-label="К списку диалогов"><i class="bi bi-arrow-left"></i></a>
                    @if ($asProducer)
                        <span class="avatar">{{ $conversation->buyer->initials() }}</span>
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate">{{ $conversation->buyer->name }}</div>
                            <div class="small text-secondary">Покупатель</div>
                        </div>
                    @else
                        <x-producer-logo :producer="$conversation->producer" size="sm" />
                        <div class="min-w-0">
                            <a href="{{ route('producers.show', $conversation->producer->slug) }}" class="fw-bold text-reset text-truncate d-block">{{ $conversation->producer->name }}</a>
                            <div class="small text-secondary">Производитель</div>
                        </div>
                    @endif
                </div>

                <div class="d-flex flex-column flex-grow-1" style="min-height: 0" data-chat
                     data-poll-url="{{ route('messages.poll', $conversation) }}" data-last-id="{{ $lastId }}">
                    <div class="chat-messages" data-chat-messages>
                        @php($previousDate = null)
                        @forelse ($messages as $message)
                            @php($date = $message->created_at->translatedFormat('j F'))
                            @if ($date !== $previousDate)
                                <div class="chat-date">{{ $message->created_at->isToday() ? 'Сегодня' : $date }}</div>
                                @php($previousDate = $date)
                            @endif
                            @php($mine = $message->sender_id === auth()->id())
                            <div class="chat-message {{ $mine ? 'is-mine' : '' }}" data-message-id="{{ $message->id }}">
                                <div class="chat-message__bubble">{{ $message->body }}</div>
                                <div class="chat-message__meta">
                                    {{ $message->created_at->format('H:i') }}
                                    @if ($mine)
                                        <i class="bi {{ $message->read_at ? 'bi-check2-all text-primary' : 'bi-check2' }}" data-read-status title="{{ $message->read_at ? 'Прочитано' : 'Доставлено' }}"></i>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-secondary small my-auto" data-chat-empty>
                                <i class="bi bi-chat-heart fs-2 d-block mb-2 text-muted-2"></i>
                                Напишите первое сообщение
                            </div>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('messages.store', $conversation) }}" class="chat-form" data-chat-form data-no-loading>
                        @csrf
                        <textarea name="body" rows="1" maxlength="2000" class="form-control" placeholder="Сообщение… (Enter — отправить)" required>{{ session('chat_draft') }}</textarea>
                        <button type="submit" class="btn btn-primary btn-icon" aria-label="Отправить"><i class="bi bi-send"></i></button>
                    </form>
                </div>
            @else
                <div class="d-flex flex-column align-items-center justify-content-center text-center text-secondary h-100 p-5">
                    <i class="bi bi-chat-square-dots fs-1 mb-2 text-muted-2"></i>
                    <div class="fw-semibold">Выберите диалог</div>
                    <div class="small">Сообщения обновляются автоматически каждые несколько секунд.</div>
                </div>
            @endif
        </div>
    </div>
@endsection
