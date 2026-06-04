@extends('layouts.cabinet')

@section('cabinet_content')
<h3 class="mb-4">Заказы</h3>

<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Статус</label>
            <select name="status" class="form-select">
                <option value="">Все</option>
                <option value="pending" @selected(request('status') === 'pending')>Ожидает оплаты</option>
                <option value="paid" @selected(request('status') === 'paid')>Оплачен</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Email пользователя</label>
            <input type="text" name="email" class="form-control" value="{{ request('email') }}" placeholder="user@mail.com">
        </div>

        <div class="col-md-2">
            <label class="form-label">Дата от</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>

        <div class="col-md-2">
            <label class="form-label">Дата до</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>

        <div class="col-md-2 d-flex align-items-end gap-2">
            <button class="btn btn-brand w-100" type="submit">Фильтр</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Сброс</a>
        </div>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Книги / страницы</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Платежи</th>
                    <th>Дата</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>
                            {{ $order->user->name ?? '—' }}<br>
                            <span class="text-muted small">{{ $order->user->email ?? '' }}</span>
                        </td>
                        <td>
                            @foreach($order->items as $item)
                                <div class="mb-2">
                                    <strong>{{ $item->book->title ?? 'Книга удалена' }}</strong><br>
                                    <span class="text-muted small">
                                        Страницы: {{ $item->page_from }}–{{ $item->page_to }}
                                        ({{ $item->pages_count }} стр.)
                                    </span>
                                </div>
                            @endforeach
                        </td>
                        <td>{{ number_format($order->total, 2, '.', ' ') }} сом</td>
                        <td>
                            @if($order->status === 'paid')
                                <span class="badge bg-success">Оплачен</span>
                            @else
                                <span class="badge bg-warning text-dark">Ожидает</span>
                            @endif
                        </td>
                        <td>
                            @forelse($order->payments as $payment)
                                <div class="small">
                                    {{ $payment->provider }} /
                                    {{ $payment->transaction_id }}<br>
                                    {{ number_format($payment->amount, 2, '.', ' ') }} сом
                                </div>
                            @empty
                                <span class="text-muted small">Нет платежей</span>
                            @endforelse
                        </td>
                        <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Заказов пока нет</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</div>
@endsection