<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromoCodeRequest;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(): View
    {
        return view('admin.promo-codes.index', ['promoCodes' => PromoCode::latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.promo-codes.form', ['promoCode' => new PromoCode(['is_active' => true])]);
    }

    public function store(PromoCodeRequest $request): RedirectResponse
    {
        $promo = PromoCode::create($request->validated());

        return redirect()->route('admin.promo-codes.index')->with('success', 'Промокод '.$promo->code.' создан.');
    }

    public function edit(PromoCode $promoCode): View
    {
        return view('admin.promo-codes.form', compact('promoCode'));
    }

    public function update(PromoCodeRequest $request, PromoCode $promoCode): RedirectResponse
    {
        $promoCode->update($request->validated());

        return redirect()->route('admin.promo-codes.index')->with('success', 'Промокод '.$promoCode->code.' сохранён.');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $promoCode->delete();

        return redirect()->route('admin.promo-codes.index')->with('success', 'Промокод '.$promoCode->code.' удалён.');
    }
}
