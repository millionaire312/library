@extends('layouts.cabinet')

@section('cabinet_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Админка — Рубрики</h3>
        <div class="text-muted">Основные рубрики и подрубрики</div>
    </div>

    <a href="{{ route('admin.categories.create') }}" class="btn btn-brand">+ Добавить рубрику</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Родитель</th>
                    <th>Книг</th>
                    <th>Тип</th>
					<th>Порядок</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>
                            @if($category->parent_id)
                                <span class="text-muted">—</span>
                            @endif
                            {{ $category->name }}
                        </td>
                        <td>{{ $category->parent->name ?? '—' }}</td>
                        <td>{{ $category->books_count }}</td>
                        <td>
                            @if($category->parent_id)
                                <span class="badge bg-info text-dark">Подрубрика</span>
                            @else
                                <span class="badge bg-primary">Основная</span>
                            @endif
                        </td>
						<td>{{ $category->sort_order }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">
                                    Редактировать
                                </a>

                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Удалить рубрику?');">
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
                        <td colspan="6" class="text-center text-muted">Рубрик пока нет</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection