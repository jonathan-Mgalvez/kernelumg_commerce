<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfferRequest;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function index(): View
    {
        $offers = Offer::with('product')->latest()->paginate(15);

        return view('admin.offers.index', compact('offers'));
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.offers.create', compact('products'));
    }

    public function store(OfferRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        Offer::create($data);

        return redirect()->route('admin.offers.index')->with('success', 'Oferta programada exitosamente.');
    }

    public function edit(Offer $offer): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('admin.offers.edit', compact('offer', 'products'));
    }

    public function update(OfferRequest $request, Offer $offer): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        $offer->update($data);

        return redirect()->route('admin.offers.index')->with('success', 'Oferta actualizada exitosamente.');
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        $offer->delete();

        return redirect()->route('admin.offers.index')->with('success', 'Oferta eliminada correctamente.');
    }
}