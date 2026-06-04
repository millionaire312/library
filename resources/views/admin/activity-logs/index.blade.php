@extends('layouts.cabinet')

@section('cabinet_content')
<h3 class="mb-4">Журнал действий</h3>

<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Действие</label>
            <select name="action" class="form-select">
                <option value="">Все</option>
                <option value="created" @selected(request('action') === 'created')>Создание</option>
                <option value="updated" @selected(request('action') === 'updated')>Изменение</option>
                <option value="deleted" @selected(request('action') === 'deleted')>Удаление</option>
				<option value="downloaded" @selected(request('action') === 'downloaded')>Скачивание</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Объект</label>
            <select name="model" class="form-select">
                <option value="">Все</option>
                <option value="Book" @selected(request('model') === 'Book')>Книга</option>
                <option value="Category" @selected(request('model') === 'Category')>Рубрика</option>
                <option value="User" @selected(request('model') === 'User')>Пользователь</option>
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Пользователь</label>
            <input type="text" name="user" class="form-control" value="{{ request('user') }}" placeholder="Имя или email">
        </div>

        <div class="col-md-2 d-flex align-items-end gap-2">
            <button class="btn btn-brand w-100" type="submit">Фильтр</button>
            <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">Сброс</a>
        </div>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Пользователь</th>
                    <th>Действие</th>
                    <th>Объект</th>
                    <th>Название</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            {{ $log->user->name ?? 'Система' }}<br>
                            <span class="text-muted small">{{ $log->user->email ?? '' }}</span>
                        </td>
                        <td>
                            @if($log->action === 'created')
                                <span class="badge bg-success">{{ $log->description }}</span>
                            @elseif($log->action === 'updated')
                                <span class="badge bg-warning text-dark">{{ $log->description }}</span>
                            @elseif($log->action === 'deleted')
                                <span class="badge bg-danger">{{ $log->description }}</span>
                            @elseif($log->action === 'downloaded')
								<span class="badge bg-info text-dark">{{ $log->description }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $log->description }}</span>
							@endif
                        </td>
                        <td>{{ $log->model }}</td>
                        <td>{{ $log->title }}</td>
                        <td>{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Журнал пока пуст</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
</div>
@endsection