<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home', [
            'categories' => Category::withCount('products')->orderBy('name')->get(),
            'featured' => Product::active()->where('featured', true)->with('category', 'activeOffer')->take(6)->get(),
        ]);
    }

    public function about()
    {
        return view('pages.about');
    }
}
