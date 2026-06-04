<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')
            ->withCount('books')
            ->orderBy('parent_id')
			->orderBy('sort_order')
			->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'parent_id' => ['nullable', 'exists:categories,id'],
			'sort_order' => ['nullable', 'integer', 'min:0'],
		]);
		
		$validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = Category::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model' => 'Category',
            'model_id' => $category->id,
            'title' => $category->name,
            'ip' => request()->ip(),
            'description' => 'Добавлена рубрика',
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Рубрика успешно добавлена.');
    }

    public function edit(Category $category)
    {
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'parent_id' => ['nullable', 'exists:categories,id'],
			'sort_order' => ['nullable', 'integer', 'min:0'],
		]);
		$validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category->update($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model' => 'Category',
            'model_id' => $category->id,
            'title' => $category->name,
            'ip' => request()->ip(),
            'description' => 'Изменена рубрика',
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Рубрика успешно обновлена.');
    }

    public function destroy(Category $category)
    {
        if ($category->books()->count() > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Нельзя удалить рубрику, пока в ней есть книги.');
        }

        if ($category->children()->count() > 0) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Нельзя удалить рубрику, пока у неё есть подрубрики.');
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'model' => 'Category',
            'model_id' => $category->id,
            'title' => $category->name,
            'ip' => request()->ip(),
            'description' => 'Удалена рубрика',
        ]);

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Рубрика удалена.');
    }
}