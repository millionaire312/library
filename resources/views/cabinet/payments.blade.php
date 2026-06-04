@extends('layouts.cabinet')

@section('cabinet_content')
<h3 class="mb-4">История платежей</h3>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Заказ</th>
                    <th>Провайдер</th>
                    <th>Transaction ID</th>
                    <th>Статус</th>
                    <th>Сумма</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                        <td>#{{ $payment->order_id }}</td>
                        <td>{{ $payment->provider }}</td>
                        <td>{{ $payment->transaction_id }}</td>
                        <td>
                            <span class="badge bg-success">{{ $payment->status }}</span>
                        </td>
                        <td>{{ number_format($payment->amount, 2, '.', ' ') }} сом</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Платежей пока нет
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection