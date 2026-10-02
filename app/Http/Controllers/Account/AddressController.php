<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Адреса доставки покупателя.
 */
class AddressController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.addresses.index', [
            'addresses' => $request->user()->addresses()->with('city')->orderByDesc('is_default')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('account.addresses.form', ['address' => new Address(['distance_km' => 7]), 'cities' => City::active()->get()]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $address = $user->addresses()->create($request->validated());

        // Первый адрес автоматически становится основным
        if ($request->boolean('is_default') || $user->addresses()->count() === 1) {
            $this->makeDefault($address);
        }

        return redirect()->route('account.addresses.index')->with('success', 'Адрес добавлен.');
    }

    public function edit(Address $address): View
    {
        $this->authorize('update', $address);

        return view('account.addresses.form', ['address' => $address, 'cities' => City::active()->get()]);
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);
        $address->update($request->validated());

        if ($request->boolean('is_default')) {
            $this->makeDefault($address);
        }

        return redirect()->route('account.addresses.index')->with('success', 'Адрес сохранён.');
    }

    public function destroy(Address $address): RedirectResponse
    {
        $this->authorize('delete', $address);
        $address->delete();

        return redirect()->route('account.addresses.index')->with('success', 'Адрес удалён.');
    }

    private function makeDefault(Address $address): void
    {
        Address::where('user_id', $address->user_id)->whereKeyNot($address->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }
}
