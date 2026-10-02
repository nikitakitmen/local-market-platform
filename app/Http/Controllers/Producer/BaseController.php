<?php

namespace App\Http\Controllers\Producer;

use App\Http\Controllers\Controller;
use App\Models\Producer;
use Illuminate\Http\Request;

/**
 * Общая часть контроллеров кабинета производителя: получение профиля текущего пользователя.
 */
abstract class BaseController extends Controller
{
    protected function producer(Request $request): Producer
    {
        return $request->user()->producer ?? abort(403, 'Профиль производителя не найден.');
    }
}
