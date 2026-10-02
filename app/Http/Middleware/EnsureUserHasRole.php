<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Проверка роли пользователя.
 *
 * Использование в маршрутах: ->middleware('role:admin') или ->middleware('role:admin,operator').
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole(...$roles)) {
            abort(403, 'У вас нет доступа к этому разделу.');
        }

        return $next($request);
    }
}
