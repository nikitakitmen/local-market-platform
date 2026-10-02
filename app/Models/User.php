<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'city_id',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // ---------------------------------------------------------------------
    // Связи
    // ---------------------------------------------------------------------

    /** Профиль производителя (есть только у тех, кто подавал заявку). */
    public function producer(): HasOne
    {
        return $this->hasOne(Producer::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /** Заказы, оформленные пользователем как покупателем. */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Доставки, закреплённые за курьером. */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'courier_id');
    }

    public function producerApplications(): HasMany
    {
        return $this->hasMany(ProducerApplication::class);
    }

    /** Диалоги, в которых пользователь участвует как покупатель. */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    // ---------------------------------------------------------------------
    // Роли
    // ---------------------------------------------------------------------

    public function hasRole(UserRole|string ...$roles): bool
    {
        foreach ($roles as $role) {
            $value = $role instanceof UserRole ? $role->value : $role;

            if ($this->role->value === $value) {
                return true;
            }
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isOperator(): bool
    {
        return $this->role === UserRole::Operator;
    }

    /** Сотрудник платформы с доступом к панели управления. */
    public function isStaff(): bool
    {
        return $this->hasRole(UserRole::Admin, UserRole::Operator);
    }

    public function isCourier(): bool
    {
        return $this->role === UserRole::Courier;
    }

    public function isProducer(): bool
    {
        return $this->role === UserRole::Producer;
    }

    /**
     * Может ли пользователь делать покупки.
     * Производитель остаётся покупателем: он не теряет доступ к своим заказам после одобрения заявки.
     */
    public function canShop(): bool
    {
        return $this->hasRole(UserRole::Buyer, UserRole::Producer);
    }

    /** Главная страница кабинета в зависимости от роли. */
    public function homeUrl(): string
    {
        return match ($this->role) {
            UserRole::Admin, UserRole::Operator => route('admin.dashboard'),
            UserRole::Producer => route('producer.dashboard'),
            UserRole::Courier => route('courier.available'),
            default => route('account.dashboard'),
        };
    }

    /** Раскладка (layout) личного кабинета для общих страниц: профиль, уведомления. */
    public function panelLayout(): string
    {
        return match ($this->role) {
            UserRole::Producer => 'layouts.producer',
            UserRole::Courier => 'layouts.courier',
            UserRole::Admin, UserRole::Operator => 'layouts.admin',
            default => 'layouts.account',
        };
    }

    // ---------------------------------------------------------------------
    // Вспомогательные методы
    // ---------------------------------------------------------------------

    /** Количество непрочитанных сообщений во всех диалогах пользователя. */
    public function unreadMessagesCount(): int
    {
        $producerId = $this->producer?->id;

        return Message::query()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $this->id)
            ->whereHas('conversation', function ($query) use ($producerId) {
                $query->where('buyer_id', $this->id);

                if ($producerId) {
                    $query->orWhere('producer_id', $producerId);
                }
            })
            ->count();
    }

    public function initials(): string
    {
        return mb_strtoupper(mb_substr($this->name, 0, 1));
    }
}
