<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** CRUD principal del sistema: productos. */
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->query('categoria'), fn ($q, $c) => $q->where('category_id', $c))
            ->when(trim((string) $request->query('q')), fn ($q, $s) => $q->where(
                fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")
            ))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['unit' => 'unidad', 'active' => true]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);
        $data['image'] = $request->file('image')?->store('productos', 'public');
        Product::create($data);

        return redirect()->route('admin.products.index')->with('status', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validar($request, $product);

        if ($request->hasFile('image')) {
            $this->borrarImagen($product);
            $data['image'] = $request->file('image')->store('productos', 'public');
        } else {
            unset($data['image']);
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('status', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        if (! $this->eliminar($product)) {
            return back()->with('status', 'El producto está en cotizaciones o pedidos; se desactivó en lugar de eliminarse.');
        }

        return back()->with('status', 'Producto eliminado.');
    }

    /** Acciones sobre varios productos seleccionados en el listado. */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
            'accion' => ['required', Rule::in(['activar', 'desactivar', 'precio', 'stock', 'eliminar'])],
            'valor' => ['nullable', 'required_if:accion,precio,stock', 'numeric', 'between:-90,100000'],
        ], ['ids.required' => 'Selecciona al menos un producto.', 'valor.required_if' => 'Indica el valor para esta acción.']);

        $products = Product::whereIn('id', $data['ids'])->get();
        $valor = (float) ($data['valor'] ?? 0);
        $desactivados = 0;

        // Uno por uno (no con un UPDATE masivo) para que cada cambio quede en la auditoría.
        foreach ($products as $p) {
            match ($data['accion']) {
                'activar' => $p->update(['active' => true]),
                'desactivar' => $p->update(['active' => false]),
                'precio' => $p->update(['price' => max(0, round($p->price * (1 + $valor / 100), 2))]),
                'stock' => $p->update(['stock' => max(0, $p->stock + (int) $valor)]),
                'eliminar' => $this->eliminar($p) ?: $desactivados++,
            };
        }

        $mensaje = match ($data['accion']) {
            'activar' => 'activados',
            'desactivar' => 'desactivados',
            'precio' => "con precio ajustado {$valor}%",
            'stock' => 'con stock ajustado',
            'eliminar' => 'eliminados',
        };

        return back()->with('status', ($products->count() - $desactivados)." producto(s) {$mensaje}."
            .($desactivados ? " {$desactivados} se desactivaron porque tienen cotizaciones o pedidos." : ''));
    }

    /** Borra el producto, o lo desactiva si tiene historial. Devuelve true si se borró. */
    private function eliminar(Product $product): bool
    {
        if ($product->quoteItems()->exists() || $product->orderItems()->exists()) {
            $product->update(['active' => false]);

            return false;
        }

        $this->borrarImagen($product);
        $product->delete();

        return true;
    }

    private function validar(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:30', Rule::unique('products')->ignore($product)],
            'name' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'standard' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'unit' => ['required', 'string', 'max:20'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
        $data['featured'] = $request->boolean('featured');
        $data['active'] = $request->boolean('active');

        return $data;
    }

    /** Solo borra imágenes subidas (no las de /public/img de los seeders). */
    private function borrarImagen(Product $product): void
    {
        if ($product->image && ! str_starts_with($product->image, '/')) {
            Storage::disk('public')->delete($product->image);
        }
    }
}
