<?php

namespace App\Http\Controllers\Producer;

use App\Http\Requests\Producer\LocationRequest;
use App\Models\ProducerLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Торговые точки производителя (могут быть пунктами самовывоза).
 */
class LocationController extends BaseController
{
    public function index(Request $request): View
    {
        return view('producer.locations.index', [
            'locations' => $this->producer($request)->locations()->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('producer.locations.form', ['location' => new ProducerLocation(['is_pickup_point' => true])]);
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        $this->producer($request)->locations()->create($request->validated());

        return redirect()->route('producer.locations.index')->with('success', 'Торговая точка добавлена.');
    }

    public function edit(ProducerLocation $location): View
    {
        $this->authorize('update', $location);

        return view('producer.locations.form', compact('location'));
    }

    public function update(LocationRequest $request, ProducerLocation $location): RedirectResponse
    {
        $this->authorize('update', $location);
        $location->update($request->validated());

        return redirect()->route('producer.locations.index')->with('success', 'Торговая точка сохранена.');
    }

    public function destroy(ProducerLocation $location): RedirectResponse
    {
        $this->authorize('delete', $location);
        $location->delete();

        return redirect()->route('producer.locations.index')->with('success', 'Торговая точка удалена.');
    }
}
