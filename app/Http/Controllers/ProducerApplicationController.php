<?php

namespace App\Http\Controllers;

use App\Enums\ProducerStatus;
use App\Http\Requests\ProducerProfileRequest;
use App\Models\City;
use App\Services\ProducerApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Заявка «Стать производителем».
 */
class ProducerApplicationController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isProducer()) {
            return redirect()->route('producer.dashboard');
        }

        return view('producer-application.create', [
            'producer' => $user->producer,
            'application' => $user->producerApplications()->latest()->first(),
            'cities' => City::active()->get(),
        ]);
    }

    public function store(ProducerProfileRequest $request, ProducerApplicationService $service): RedirectResponse
    {
        $user = $request->user();

        if ($user->isProducer()) {
            return redirect()->route('producer.dashboard');
        }

        if ($user->producer?->status === ProducerStatus::Pending) {
            return back()->with('info', 'Ваша заявка уже на проверке.');
        }

        $service->submit($user, $request->validated(), $request->file('logo'));

        return redirect()->route('producer-application.create')
            ->with('success', 'Заявка отправлена! Администратор проверит данные и сообщит о решении.');
    }
}
