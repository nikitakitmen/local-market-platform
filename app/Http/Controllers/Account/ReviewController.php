<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = $request->user()->reviews()
            ->with(['product', 'producer'])
            ->latest()
            ->paginate(10);

        return view('account.reviews', compact('reviews'));
    }
}
