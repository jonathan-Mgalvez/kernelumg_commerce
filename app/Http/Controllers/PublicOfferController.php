<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicOfferController extends Controller
{
    public function index(Request $request): View
    {
        $offersQuery = Product::where('is_active', true)
            ->whereHas('activeOffer')
            ->with(['category', 'activeOffer']);

        if ($request->filled('category')) {
            $offersQuery->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        $featuredOffers = $offersQuery->latest()->paginate(12)->withQueryString();

        return view('offers.index', compact('featuredOffers'));
    }
}