@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <h3 class="mb-2">QR-оплата заказа #{{ $order->id }}</h3>
            <div class="text-muted mb-3">
                Это демо-оплата. Позже сюда подключим реальный QR банка.
            </div>

            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="border rounded-4 d-flex align-items-center justify-content-center bg-light"
                         style="height:260px;">
                        <div class="text-center">
                            <div style="font-size:54px;">▦</div>
                            <div class="fw-semibold">QR DEMO</div>
                            <div class="small text-muted">Заказ #{{ $order->id }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="mb-2">
                        <strong>Получатель:</strong> Электронная библиотека
                    </div>

                    <div class="mb-2">
                        <strong>Назначение:</strong> Оплата страниц, заказ #{{ $order->id }}
                    </div>

                    <div class="mb-2">
                        <strong>Сумма:</strong> {{ number_format($order->total, 2, '.', ' ') }} сом
                    </div>

                    <div class="mb-2">
                        <strong>Статус:</strong>
                        @if($order->status === 'paid')
                            <span class="badge bg-success">Оплачен</span>
                        @else
                            <span class="badge bg-warning text-dark">Ожидает оплаты</span>
                        @endif
                    </div>

                    <hr>

                    @if($order->status !== 'paid')
                        <form method="POST" action="{{ route('orders.qr-confirm', [$order, $order->access_token]) }}">
                            @csrf
                            <button type="submit" class="btn btn-brand w-100">
                                Демо: подтвердить оплату
                            </button>
                        </form>
                    @else
                        <a href="{{ route('orders.guest-downloads', [$order, $order->access_token]) }}" class="btn btn-success w-100">
                            Перейти к скачиванию
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection