<?php

namespace App\Http\Controllers\Admin;

use App\BlogCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlatformBlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::query()
            ->withCount('posts')
            ->orderBy('name')
            ->paginate(30);

        return view('admin.platform.blog.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.platform.blog.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_categories', 'slug')],
        ]);

        $slug = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : BlogCategory::uniqueSlugFromName($data['name']);

        BlogCategory::create([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('admin.platform.blog.categories.index')
            ->with('flash', 'Categoría creada.');
    }

    public function edit(BlogCategory $category)
    {
        return view('admin.platform.blog.categories.edit', compact('category'));
    }

    public function update(Request $request, BlogCategory $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_categories', 'slug')->ignore($category->id)],
        ]);

        $slug = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : BlogCategory::uniqueSlugFromName($data['name'], $category->id);

        $category->update([
            'name' => $data['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('admin.platform.blog.categories.index')
            ->with('flash', 'Categoría actualizada.');
    }

    public function destroy(BlogCategory $category)
    {
        if ($category->posts()->exists()) {
            return back()->with('flash_error', 'No se puede eliminar: hay artículos asignados.');
        }

        $category->delete();

        return redirect()
            ->route('admin.platform.blog.categories.index')
            ->with('flash', 'Categoría eliminada.');
    }
}
