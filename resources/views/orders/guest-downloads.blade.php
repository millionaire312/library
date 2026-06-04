@extends('layouts.app')

@section('content')
<h3 class="mb-4">Скачивание страниц заказа #{{ $order->id }}</h3>

<div class="card p-3">
    @foreach($order->items as $item)
        <div class="mb-3">
            <strong>{{ $item->book->title }}</strong><br>
            Страницы: {{ $item->page_from }}–{{ $item->page_to }}<br>
            Скачиваний: {{ $item->downloads }} / 5

            <div class="mt-2">
                <a href="{{ route('orders.guest-download', [$order, $item, $order->access_token]) }}"
                   class="btn btn-success btn-sm">
                    Скачать страницы
                </a>
            </div>
        </div>
    @endforeach
</div>
@endsection