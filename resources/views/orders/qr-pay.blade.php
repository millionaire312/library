@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
	<h3 class="mb-2">QR-оплата заказа #{{ $order->id }}</h3>
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="border rounded-4 d-flex align-items-center justify-content-center bg-light" style="height:260px;">
			<div class="text-center p-3">
    @if($order->status === 'paid')
        <div style="font-size:54px;">✅</div>
        <div class="fw-semibold text-success">Оплачено</div>
        <div class="small text-muted">Заказ #{{ $order->id }}</div>
    @elseif($finikPayment && $finikPayment->payment_url)
        <div class="mb-2">
@php
    $finikQrPayload = null;

    if ($finikPayment && $finikPayment->payment_url) {
        $urlParts = parse_url($finikPayment->payment_url);

        if (!empty($urlParts['fragment'])) {
            $finikQrPayload = explode('?', $urlParts['fragment'])[0];
        }
    }
@endphp

@if($finikQrPayload)
	{!! QrCode::size(260)->margin(2)->generate('https://qr.finik.kg/b5a6ab6e-acba-4785-880a-898935663cbd?type=t&') !!}
@else
    <div class="text-muted small">
        QR временно недоступен
    </div>
@endif
        </div>
    @else
        <div style="font-size:54px;">▦</div>
        <div class="fw-semibold">QR создаётся</div>
        <div class="small text-muted">Обновите страницу через несколько секунд</div>
    @endif
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
    @if($finikPayment && $finikPayment->payment_url)
        <a href="{{ $finikPayment->payment_url }}"
           class="btn btn-success w-100 mb-2"
           target="_blank">
            Открыть страницу оплаты Finik
        </a>
    @endif

@else
    <a href="{{ route('orders.guest-downloads', [$order, $order->access_token]) }}" class="btn btn-success w-100">
        Перейти к скачиванию
    </a>
@endif                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<style>
    .qr-box {
        width: 200px;
        max-width: 100%;
        overflow: hidden;
    }

    .qr-box svg {
        width: 100%;
        height: auto;
        display: block;
    }
</style>
