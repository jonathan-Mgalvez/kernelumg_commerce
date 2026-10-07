<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        $featuredProducts = Product::where('is_active', true)
            ->with(['category', 'activeOffer'])
            ->latest()
            ->take(6)
            ->get();

        return view('pages.home', compact('featuredProducts'));
    }

    public function about(): View
    {
        return view('pages.about');
    }
}