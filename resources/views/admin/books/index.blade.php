@extends('layouts.cabinet')

@section('cabinet_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Админка — Книги</h3>
        <div class="text-muted">Управление книгами библиотеки</div>
    </div>

    <a href="{{ route('admin.books.create') }}" class="btn btn-brand">+ Добавить книгу</a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Обложка</th>
                    <th>Название</th>
                    <th>Автор</th>
                    <th>Год</th>
                    <th>Рубрика</th>
                    <th>Страниц</th>
                    <th>Цена</th>
                    <th>Скидка</th>
                    <th>PDF</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($books as $book)
                    <tr>
                        <td>{{ $book->id }}</td>

                        <td>
                            @if($book->cover)
                                <img src="{{ asset('storage/' . $book->cover) }}" alt="cover" style="width:60px;height:80px;object-fit:cover;border-radius:8px;">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author }}</td>
                        <td>{{ $book->year }}</td>
                        <td>{{ $book->category->name ?? '—' }}</td>
                        <td>{{ $book->pages }}</td>
                        <td>{{ $book->price_per_page }}</td>

                        <td>
                            @if($book->is_discount)
                                <span class="badge bg-success">Да</span>
                            @else
                                <span class="badge bg-secondary">Нет</span>
                            @endif
                        </td>

                        <td>
                            @if($book->pdf)
                                <span class="small text-success">Есть</span>
                            @else
                                <span class="small text-muted">—</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-outline-primary">
                                    Редактировать
                                </a>

                                <form method="POST" action="{{ route('admin.books.destroy', $book) }}" onsubmit="return confirm('Удалить книгу?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">Книг пока нет</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection