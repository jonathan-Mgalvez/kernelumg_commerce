<?php

namespace App\Http\Controllers;

use App\Models\Product;

/** Página pública de ofertas vigentes. */
class OfferController extends Controller
{
    public function index()
    {
        $products = Product::active()
            ->whereHas('offers', fn ($q) => $q->vigente())
            ->with('category', 'activeOffer')
            ->get()
            ->sortByDesc(fn ($p) => $p->activeOffer->discount_rate);

        return view('offers.index', compact('products'));
    }
}
