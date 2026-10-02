<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Уведомление внутри сайта (сохраняется в таблицу notifications).
 *
 * Пример: $user->notify(new SiteNotification('Заказ принят', 'Заказ LM-000012 принят', route(...), 'bi-check-circle'));
 * Чтобы дополнительно отправлять email, достаточно добавить канал 'mail' в via() и метод toMail().
 */
class SiteNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public string $icon = 'bi-bell',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'icon' => $this->icon,
        ];
    }
}
