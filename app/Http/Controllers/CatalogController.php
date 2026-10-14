<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()->with('category', 'activeOffer');

        if ($cat = $request->query('categoria')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $cat));
        }

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w->where('name', 'like', "%$q%")
                ->orWhere('sku', 'like', "%$q%")
                ->orWhere('standard', 'like', "%$q%"));
        }

        match ($request->query('orden')) {
            'precio_asc' => $query->orderBy('price'),
            'precio_desc' => $query->orderByDesc('price'),
            'nombre' => $query->orderBy('name'),
            default => $query->orderByDesc('stock'),
        };

        return view('catalog.index', [
            'products' => $query->paginate(9)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->active, 404);

        return view('catalog.show', [
            'product' => $product->load('category', 'activeOffer'),
            'related' => Product::active()->with('category', 'activeOffer')->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)->take(3)->get(),
        ]);
    }
}
