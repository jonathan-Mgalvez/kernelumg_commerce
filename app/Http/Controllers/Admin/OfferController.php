<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request)
    {
        $offers = Offer::with('product')
            ->when($request->query('estado') === 'vigente', fn ($q) => $q->vigente())
            ->latest('starts_at')->paginate(15)->withQueryString();

        return view('admin.offers.index', compact('offers'));
    }

    public function create(Request $request)
    {
        return view('admin.offers.form', [
            'offer' => new Offer([
                'product_id' => $request->query('producto'),
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addDays(15)->endOfDay(),
                'active' => true,
            ]),
            'products' => Product::active()->orderBy('name')->get(['id', 'sku', 'name', 'price']),
        ]);
    }

    public function store(Request $request)
    {
        Offer::create($this->validar($request));

        return redirect()->route('admin.offers.index')->with('status', 'Oferta creada. Ya se refleja en el catálogo y el carrito.');
    }

    public function edit(Offer $offer)
    {
        return view('admin.offers.form', [
            'offer' => $offer,
            'products' => Product::orderBy('name')->get(['id', 'sku', 'name', 'price']),
        ]);
    }

    public function update(Request $request, Offer $offer)
    {
        $offer->update($this->validar($request));

        return redirect()->route('admin.offers.index')->with('status', 'Oferta actualizada.');
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();

        return back()->with('status', 'Oferta eliminada.');
    }

    private function validar(Request $request): array
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'title' => ['nullable', 'string', 'max:100'],
            'discount_rate' => ['required', 'numeric', 'min:1', 'max:90'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);
        $data['active'] = $request->boolean('active');

        return $data;
    }
}
