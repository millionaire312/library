@extends('layouts.cabinet')

@section('cabinet_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Редактировать рубрику</h3>
        <div class="text-muted">Изменение названия и родителя</div>
    </div>

    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-brand">← Назад</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card p-4">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Название рубрики</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Родительская рубрика</label>
            <select name="parent_id" class="form-select">
                <option value="">Нет — основная рубрика</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
        </div>
		<div class="mb-3">
			<label class="form-label">Порядок отображения</label>
			<input type="number" name="sort_order" class="form-control"
				   value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">
			<div class="text-muted small mt-1">
				Чем меньше число, тем выше рубрика в меню. Например: 1, 2, 3.
			</div>
		</div>

        <button type="submit" class="btn btn-brand">Сохранить изменения</button>
    </form>
</div>
@endsection