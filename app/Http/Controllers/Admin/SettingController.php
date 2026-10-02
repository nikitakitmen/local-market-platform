<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Настройки сайта: комиссия платформы, минимальная сумма заказа, тарифы доставки.
 */
class SettingController extends Controller
{
    public function edit(): View
    {
        $values = collect(Setting::DEFAULTS)->map(fn ($default, $key) => Setting::get($key));

        return view('admin.settings', ['values' => $values]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        Setting::setMany($request->validated());

        return back()->with('success', 'Настройки сохранены.');
    }
}
