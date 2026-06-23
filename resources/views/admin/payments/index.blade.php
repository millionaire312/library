@extends('layouts.cabinet')

@section('cabinet_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Журнал платежей</h3>
        <div class="text-muted">Покупки гостей и зарегистрированных пользователей</div>
    </div>
</div>

<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Поиск</label>
            <input type="text"
                   name="q"
                   class="form-control"
                   value="{{ request('q') }}"
                   placeholder="ID, имя, email, телефон">
        </div>

        <div class="col-md-3">
            <label class="form-label">Статус</label>
            <select name="status" class="form-select">
                <option value="">Все</option>
                <option value="pending" @selected(request('status') === 'pending')>Ожидает оплаты</option>
                <option value="paid" @selected(request('status') === 'paid')>Оплачен</option>
            </select>
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
            <button type="submit" class="btn btn-brand w-100">Найти</button>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">Сброс</a>
        </div>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Заказ</th>
                    <th>Покупатель</th>
                    <th>Книга / страницы</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Способ</th>
                    <th>Платёж</th>
                    <th>Дата</th>
                </tr>
            </thead>

            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>

                        <td>
                            @if($order->user)
                                <span class="badge bg-primary mb-1">Пользователь</span><br>
                                {{ $order->user->name }}<br>
                                <span class="text-muted small">{{ $order->user->email }}</span>
                            @else
                                <span class="badge bg-secondary mb-1">Гость</span><br>
                                {{ $order->guest_name ?? '—' }}<br>
                                <span class="text-muted small">{{ $order->guest_email ?? '—' }}</span><br>
                                <span class="text-muted small">{{ $order->guest_phone ?? '—' }}</span>
                            @endif
                        </td>

                        <td>
                            @foreach($order->items as $item)
                                <div class="mb-2">
                                    <strong>{{ $item->book->title ?? 'Книга удалена' }}</strong><br>
                                    <span class="text-muted small">
                                        {{ $item->page_from }}–{{ $item->page_to }}
                                        / {{ $item->pages_count }} стр.
                                    </span>
                                </div>
                            @endforeach
                        </td>

                        <td>
                            <strong>{{ number_format($order->total, 2, '.', ' ') }} сом</strong>
                        </td>

                        <td>
                            @if($order->status === 'paid')
                                <span class="badge bg-success">Оплачен</span>
                            @else
                                <span class="badge bg-warning text-dark">Ожидает</span>
		                    @if($order->access_token)
    					<div class="mt-2">
        				    <a href="{{ route('orders.qr-pay', [$order, $order->access_token]) }}"
				            class="btn btn-sm btn-outline-primary" target="_blank">Продолжить оплату</a>
				        </div>
				    @endif        
			    @endif
                        </td>

                        <td>
                            {{ $order->payment_method ?? '—' }}
                        </td>

                        <td>
                            @forelse($order->payments as $payment)
                                <div class="small mb-1">
                                    <strong>{{ $payment->provider }}</strong><br>
                                    {{ $payment->transaction_id }}<br>
                                    {{ number_format($payment->amount, 2, '.', ' ') }} сом
                                </div>
                            @empty
                                <span class="text-muted small">Нет платежа</span>
                            @endforelse
                        </td>

                        <td>
                            {{ $order->created_at->format('d.m.Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Записей не найдено
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $orders->links() }}
    </div>
</div>
@endsection
