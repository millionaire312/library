@extends('layouts.cabinet')

@section('cabinet_content')
	<h3 class="mb-4">Мои покупки</h3>

	@if(session('success'))
		<div class="alert alert-success">
			{{ session('success') }}
		</div>
	@endif

	@if(session('error'))
		<div class="alert alert-danger">
			{{ session('error') }}
		</div>
	@endif

	@forelse($orders as $order)
		<div class="card mb-3 p-3">
			<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
				<div>
					<strong>Заказ #{{ $order->id }}</strong>
					<div class="text-muted small">
						{{ $order->created_at->format('d.m.Y H:i') }}
					</div>
				</div>

				<div>
					@if($order->status === 'paid')
						<span class="badge bg-success">Оплачен</span>
					@else
						<span class="badge bg-warning text-dark">Ожидает оплаты</span>
					@endif
				</div>
			</div>

			<hr>

			@foreach($order->items as $item)
				<div class="mb-3">
					<strong>{{ $item->book->title }}</strong><br>
					Страницы: <strong>{{ $item->page_from }}–{{ $item->page_to }}</strong>
					({{ $item->pages_count }} стр.) 
					
					@if($order->status === 'paid')
						<a href="{{ route('orders.download', [$order, $item]) }}" class="btn btn-sm btn-success mt-2">
							Скачать страницы
						</a>
					@endif
					<br>
					Цена за страницу: {{ number_format($item->price_per_page, 2, '.', ' ') }} сом<br>
					<strong>Сумма: {{ number_format($item->total, 2, '.', ' ') }} сом</strong>
					<div class="text-muted small">
						Скачиваний: {{ $item->downloads }} / 5
					</div>
				</div>
			@endforeach

			<hr>

			<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
				<div>
					<strong>Итого: {{ number_format($order->total, 2, '.', ' ') }} сом</strong>
				</div>

				<div>
					@if($order->status !== 'paid')
						<form method="POST" action="{{ route('orders.pay', $order) }}">
							@csrf
							<button type="submit" class="btn btn-brand">
								Оплатить
							</button>
						</form>
					@else
						<span class="text-success fw-semibold">Оплата подтверждена</span>
					@endif
				</div>
			</div>

			@if($order->payments->count())
				<div class="mt-3 small text-muted">
					Последний платёж:
					{{ $order->payments->last()->provider }} /
					{{ $order->payments->last()->transaction_id }}
				</div>
			@endif
		</div>
	@empty
		<div class="alert alert-light border">
			У вас пока нет покупок.
		</div>
	@endforelse
@endsection