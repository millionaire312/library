<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ActivityLog;
use setasign\Fpdi\Fpdi;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('category')->latest()->get();

        return view('admin.books.index', compact('books'));
    }

    public function create()
    {
        $categories = Category::with('parent')
			->orderBy('parent_id')
			->orderBy('name')
			->get();

        return view('admin.books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1000', 'max:9999'],
            'description' => ['nullable', 'string'],
            'pages' => ['required', 'integer', 'min:1'],
            'price_per_page' => ['required', 'numeric', 'min:0'],
            'is_discount' => ['nullable', 'boolean'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
        ]);

        $validated['is_discount'] = $request->has('is_discount');

        if (!$validated['is_discount']) {
            $validated['discount_price'] = null;
        }

        if ($request->hasFile('cover')) {
            $validated['cover'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('pdf')) {
			$validated['pdf'] = $request->file('pdf')->store('books', 'public');

			$filePath = storage_path('app/public/' . $validated['pdf']);

			$pdf = new Fpdi();
			$validated['pages'] = $pdf->setSourceFile($filePath);
		}

        $book = Book::create($validated);

		ActivityLog::create([
			'user_id' => auth()->id(),
			'action' => 'created',
			'model' => 'Book',
			'model_id' => $book->id,
			'title' => $book->title,
			'ip' => request()->ip(),
			'description' => 'Добавлена книга',
		]);

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Книга успешно добавлена.');
    }

    public function edit(Book $book)
    {
        $categories = Category::with('parent')
			->orderBy('parent_id')
			->orderBy('name')
			->get();

        return view('admin.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1000', 'max:9999'],
            'description' => ['nullable', 'string'],
            'pages' => ['required', 'integer', 'min:1'],
            'price_per_page' => ['required', 'numeric', 'min:0'],
            'is_discount' => ['nullable', 'boolean'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
        ]);

        $validated['is_discount'] = $request->has('is_discount');

        if (!$validated['is_discount']) {
            $validated['discount_price'] = null;
        }

        if ($request->hasFile('cover')) {
            if ($book->cover && Storage::disk('public')->exists($book->cover)) {
                Storage::disk('public')->delete($book->cover);
            }

            $validated['cover'] = $request->file('cover')->store('covers', 'public');
        }

        if ($request->hasFile('pdf')) {
			if ($book->pdf && Storage::disk('public')->exists($book->pdf)) {
				Storage::disk('public')->delete($book->pdf);
			}

			$validated['pdf'] = $request->file('pdf')->store('books', 'public');

			$filePath = storage_path('app/public/' . $validated['pdf']);

			$pdf = new Fpdi();
			$validated['pages'] = $pdf->setSourceFile($filePath);
		}

        $book->update($validated);
		
		ActivityLog::create([
			'user_id' => auth()->id(),
			'action' => 'updated',
			'model' => 'Book',
			'model_id' => $book->id,
			'title' => $book->title,
			'ip' => request()->ip(),
			'description' => 'Изменена книга',
		]);
		
        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Книга успешно обновлена.');
    }

    public function destroy(Book $book)
    {
        if ($book->cover && Storage::disk('public')->exists($book->cover)) {
            Storage::disk('public')->delete($book->cover);
        }

        if ($book->pdf && Storage::disk('public')->exists($book->pdf)) {
            Storage::disk('public')->delete($book->pdf);
        }
		
		ActivityLog::create([
			'user_id' => auth()->id(),
			'action' => 'deleted',
			'model' => 'Book',
			'model_id' => $book->id,
			'title' => $book->title,
			'ip' => request()->ip(),
			'description' => 'Удалена книга',
		]);

        $book->delete();

        return redirect()
            ->route('admin.books.index')
            ->with('success', 'Книга удалена.');
    }
}