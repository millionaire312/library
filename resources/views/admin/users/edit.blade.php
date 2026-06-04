@extends('layouts.cabinet')

@section('cabinet_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Изменить роль</h3>
        <div class="text-muted">{{ $user->name }} — {{ $user->email }}</div>
    </div>

    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-brand">← Назад</a>
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
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Роль пользователя</label>
            <select name="role" class="form-select" required>
                <option value="user" @selected(old('role', $user->role) === 'user')>user — обычный пользователь</option>
                <option value="editor" @selected(old('role', $user->role) === 'editor')>editor — редактор</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>admin — администратор</option>
            </select>
        </div>

        <button type="submit" class="btn btn-brand">Сохранить</button>
    </form>
</div>
@endsection