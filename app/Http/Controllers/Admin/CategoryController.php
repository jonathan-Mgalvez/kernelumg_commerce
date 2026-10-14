<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category(['icon' => 'category'])]);
    }

    public function store(Request $request)
    {
        Category::create($this->validar($request));

        return redirect()->route('admin.categories.index')->with('status', 'Categoría creada.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validar($request, $category));

        return redirect()->route('admin.categories.index')->with('status', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'No se puede eliminar una categoría que tiene productos.']);
        }
        $category->delete();

        return back()->with('status', 'Categoría eliminada.');
    }

    private function validar(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories')->ignore($category)],
            'icon' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        $data['slug'] = Str::slug($data['name']);

        return $data;
    }
}
