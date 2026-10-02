<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::roots()->active()->ordered()
            ->withCount(['products' => fn ($query) => $query->visible()])
            ->get();

        $popular = Product::visible()->with(['producer', 'variants'])->sort('popular')->limit(8)->get();
        $newest = Product::visible()->where('in_stock', true)->with(['producer', 'variants'])->sort('new')->limit(4)->get();

        $producers = Producer::approved()
            ->with('city')
            ->withCount(['products' => fn ($query) => $query->visible()])
            ->orderByDesc('rating')
            ->orderByDesc('reviews_count')
            ->limit(4)
            ->get();

        $stats = [
            'producers' => Producer::approved()->count(),
            'products' => Product::visible()->count(),
            'cities' => City::whereHas('producers', fn ($query) => $query->approved())->count(),
        ];

        return view('home', compact('categories', 'popular', 'newest', 'producers', 'stats'));
    }
}
