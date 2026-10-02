<?php

namespace App\Services;

use App\Enums\ProducerStatus;
use App\Enums\UserRole;
use App\Models\Producer;
use App\Models\ProducerApplication;
use App\Models\User;
use App\Notifications\SiteNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Заявки на регистрацию производителя.
 *
 * Пользователь заполняет анкету → создаётся профиль производителя со статусом pending и заявка.
 * Администратор одобряет заявку → профиль получает статус approved, а пользователь — роль «Производитель».
 */
class ProducerApplicationService
{
    public function submit(User $user, array $data, ?UploadedFile $logo = null): ProducerApplication
    {
        $application = DB::transaction(function () use ($user, $data, $logo) {
            $producer = $user->producer ?? new Producer;

            $producer->fill([
                'city_id' => $data['city_id'],
                'name' => $data['name'],
                'type' => $data['type'],
                'inn' => $data['inn'] ?? null,
                'description' => $data['description'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'address' => $data['address'],
                'status' => ProducerStatus::Pending,
            ]);
            $producer->user_id = $user->id;

            if ($logo) {
                if ($producer->logo) {
                    Storage::disk('public')->delete($producer->logo);
                }

                $producer->logo = $logo->store('producers', 'public');
            }

            $producer->save();

            return $producer->applications()->create([
                'user_id' => $user->id,
                'message' => $data['message'] ?? null,
                'status' => ProducerStatus::Pending,
            ]);
        });

        User::where('role', UserRole::Admin)->where('is_active', true)->get()
            ->each(fn (User $admin) => $admin->notify(new SiteNotification(
                'Новая заявка производителя',
                '«'.$application->producer->name.'» ожидает проверки.',
                route('admin.applications.show', $application),
                'bi-person-badge'
            )));

        return $application;
    }

    public function approve(ProducerApplication $application, User $admin): void
    {
        DB::transaction(function () use ($application, $admin) {
            $application->update([
                'status' => ProducerStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $application->producer->update([
                'status' => ProducerStatus::Approved,
                'approved_at' => now(),
            ]);

            // Покупатель становится производителем (покупательские разделы ему по-прежнему доступны)
            $application->user->update(['role' => UserRole::Producer]);
        });

        $application->user->notify(new SiteNotification(
            'Заявка производителя одобрена',
            'Добро пожаловать! Теперь вы можете добавлять товары в кабинете производителя.',
            route('producer.dashboard'),
            'bi-patch-check'
        ));
    }

    public function reject(ProducerApplication $application, User $admin, string $comment): void
    {
        DB::transaction(function () use ($application, $admin, $comment) {
            $application->update([
                'status' => ProducerStatus::Rejected,
                'admin_comment' => $comment,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $application->producer->update(['status' => ProducerStatus::Rejected]);
        });

        $application->user->notify(new SiteNotification(
            'Заявка производителя отклонена',
            'Комментарий администратора: '.$comment,
            route('producer-application.create'),
            'bi-x-octagon'
        ));
    }
}
