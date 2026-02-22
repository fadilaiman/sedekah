<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::ordered()->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori ditambahkan dengan jayanya.');
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        // Remove 'value' if present (immutable field)
        unset($validated['value']);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori dikemaskini dengan jayanya.');
    }

    public function destroy(Category $category)
    {
        if ($category->isInUse()) {
            return redirect()->back()
                ->with('error', 'Tidak boleh padam kategori yang masih digunakan oleh institusi.');
        }

        $category->delete();

        return redirect()->back()
            ->with('success', 'Kategori telah dipadamkan.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        foreach ($validated['order'] as $categoryId => $order) {
            Category::find($categoryId)?->update(['order' => $order]);
        }

        return redirect()->back()
            ->with('success', 'Susunan kategori telah dikemaskini.');
    }
}
