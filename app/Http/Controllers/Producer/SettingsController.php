<?php

namespace App\Http\Controllers\Producer;

use App\Http\Requests\ProducerProfileRequest;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Настройки профиля производителя (витрина магазина).
 */
class SettingsController extends BaseController
{
    public function edit(Request $request): View
    {
        return view('producer.settings', [
            'producer' => $this->producer($request),
            'cities' => City::active()->get(),
        ]);
    }

    public function update(ProducerProfileRequest $request): RedirectResponse
    {
        $producer = $this->producer($request);
        $producer->fill(Arr::except($request->validated(), ['logo', 'message']));

        if ($request->hasFile('logo')) {
            if ($producer->logo) {
                Storage::disk('public')->delete($producer->logo);
            }

            $producer->logo = $request->file('logo')->store('producers', 'public');
        }

        $producer->save();

        return back()->with('success', 'Настройки магазина сохранены.');
    }
}
