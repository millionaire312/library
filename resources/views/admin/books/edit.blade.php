@extends('layouts.cabinet')

@section('cabinet_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Редактировать книгу</h3>
        <div class="text-muted">Изменение данных книги</div>
    </div>

    <a href="{{ route('admin.books.index') }}" class="btn btn-outline-brand">← Назад</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-2">Проверьте форму:</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card p-4">
    <form method="POST" action="{{ route('admin.books.update', $book) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Название книги</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $book->title) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Автор</label>
                <input type="text" name="author" class="form-control" value="{{ old('author', $book->author) }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Год</label>
                <input type="number" name="year" class="form-control" value="{{ old('year', $book->year) }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Количество страниц</label>
                <input type="number" name="pages" class="form-control" value="{{ old('pages', $book->pages) }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Цена за страницу</label>
                <input type="number" step="0.01" name="price_per_page" class="form-control" value="{{ old('price_per_page', $book->price_per_page) }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Рубрика</label>
                <select name="category_id" class="form-select" required>
					@foreach($categories as $category)
						<option value="{{ $category->id }}"
							@selected(old('category_id', $book->category_id) == $category->id)>

							{{ $category->parent ? $category->parent->name . ' → ' : '' }}
							{{ $category->name }}

						</option>
					@endforeach
				</select>
            </div>

            <div class="col-12">
                <label class="form-label">Описание</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $book->description) }}</textarea>
            </div>

            <div class="col-md-4">
                <label class="form-label">Новая обложка</label>
                <input type="file" name="cover" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                @if($book->cover)
                    <div class="small text-muted mt-2">Текущая: {{ $book->cover }}</div>
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label">Новый PDF</label>
                <input type="file" name="pdf" class="form-control" accept=".pdf">
                @if($book->pdf)
                    <div class="small text-muted mt-2">Текущий: {{ $book->pdf }}</div>
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label d-block">Скидка</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="is_discount" value="1" id="is_discount" @checked(old('is_discount', $book->is_discount))>
                    <label class="form-check-label" for="is_discount">
                        Включить скидку
                    </label>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Цена со скидкой</label>
                <input type="number" step="0.01" name="discount_price" class="form-control" value="{{ old('discount_price', $book->discount_price) }}">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-brand">Сохранить изменения</button>
        </div>
    </form>
</div>
@endsection