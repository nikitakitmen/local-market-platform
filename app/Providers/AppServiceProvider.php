<?php

namespace App\Providers;

use App\View\Composers\NavigationComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Даты на русском: «2 октября», «5 минут назад»
        Carbon::setLocale(config('app.locale'));

        // Пагинация в разметке Bootstrap 5
        Paginator::useBootstrapFive();

        // В режиме разработки записываем в лог случаи «ленивой» загрузки связей (проблема N+1)
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
            logger()->warning('N+1: ленивая загрузка '.$model::class.'::'.$relation);
        });

        // Счётчики корзины, уведомлений и сообщений для шапки сайта и панелей
        View::composer('layouts.*', NavigationComposer::class);

        // Письмо восстановления пароля на русском языке
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]);

            return (new MailMessage)
                ->subject('Восстановление пароля')
                ->greeting('Здравствуйте!')
                ->line('Вы получили это письмо, потому что для вашего аккаунта был запрошен сброс пароля.')
                ->action('Сбросить пароль', $url)
                ->line('Ссылка действительна '.config('auth.passwords.users.expire').' минут.')
                ->line('Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.')
                ->salutation('Команда «'.config('app.name').'»');
        });
    }
}
