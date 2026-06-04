@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-3">
            @if($book->cover)
				<img class="cover" src="{{ asset('storage/' . $book->cover) }}" alt="{{ $book->title }}">
			@else
				<img class="cover" src="https://picsum.photos/seed/{{ $book->id }}/900/700" alt="{{ $book->title }}">
			@endif

            <div class="mt-3">
                <h4 class="mb-1">{{ $book->title }}</h4>
                <div class="text-muted">
                    {{ $book->author }} • {{ $book->year }}
                </div>

                <div class="mt-2 small text-muted">
                    Страниц: <strong>{{ $book->pages }}</strong> •
                    Рубрика: <strong>{{ $book->category->name ?? 'Без рубрики' }}</strong>
                </div>

                <div class="mt-3">
                    <div class="text-muted small">Цена за страницу:</div>
                    <div class="price-line">
                        @if($book->is_discount && $book->discount_price)
                            <span class="price-old">{{ $book->price_per_page }} сом</span>
                            <span class="price-new">{{ $book->discount_price }} сом</span>
                        @else
                            <span class="price-new">{{ $book->price_per_page }} сом</span>
                        @endif
                    </div>
                </div>

                <hr>

                <div class="small text-muted">
                    {{ $book->description ?: 'Описание пока не заполнено.' }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card p-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">Покупка диапазона страниц</div>
                    <div class="text-muted small">
                        Пользователь сам выбирает страницы. Максимум 15 страниц за один заказ.
                    </div>
                </div>
            </div>

            <div class="page-preview mt-3" style="height: 650px; padding: 0; overflow: hidden;">
				@if($book->pdf)
					<iframe
						src="{{ route('books.preview', $book) }}"
						style="width:100%; height:100%; border:0;"
					></iframe>
				@else
					<div class="d-flex align-items-center justify-content-center h-100 text-muted">
						PDF файл для предпросмотра не загружен
					</div>
				@endif
			</div>

			<div class="text-muted small mt-2">
				Показаны первые 10 страниц для предварительного просмотра.
			</div>

            <hr>

            @php
                $pagePrice = ($book->is_discount && $book->discount_price) ? $book->discount_price : $book->price_per_page;
            @endphp

            <div class="row g-3">
                <div class="col-md-7">
                    <div class="fw-semibold mb-2">Укажите диапазон страниц</div>

                    <input type="hidden" id="bookPages" value="{{ $book->pages }}">
                    <input type="hidden" id="pricePerPage" value="{{ $pagePrice }}">
                    <input type="hidden" id="maxPages" value="15">

                    <div class="card p-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-6">
                                <label class="form-label mb-1">Страница с</label>
                                <input id="rangeFrom" class="form-control" type="number" min="1" max="{{ $book->pages }}" placeholder="например: 155">
                            </div>

                            <div class="col-6">
                                <label class="form-label mb-1">по</label>
                                <input id="rangeTo" class="form-control" type="number" min="1" max="{{ $book->pages }}" placeholder="например: 165">
                            </div>

                            <div class="col-12">
                                <div class="text-muted small">
                                    Цена за 1 страницу:
                                    <strong>{{ $pagePrice }} сом</strong>
                                    • Всего страниц в книге:
                                    <strong>{{ $book->pages }}</strong>
                                    • Ограничение:
                                    <strong>до 15 страниц</strong>
                                </div>
                            </div>

                            <div class="col-12">
                                <div id="rangeMsg" class="alert alert-warning d-none mb-0"></div>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="page-lock">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <div class="fw-semibold">Вы выбрали:</div>
                                            <div class="text-muted small">
                                                Диапазон: <strong id="rangeSummary">—</strong>
                                            </div>
                                        </div>
                                        <span class="badge bg-success">Подготовить к покупке</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-muted small mt-2">
                        Пример: если выбрать страницы 155–165, система посчитает количество автоматически.
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="fw-semibold mb-2">Корзина</div>
                    <div class="card p-3">
                        <div class="d-flex justify-content-between">
                            <div class="text-muted">Выбрано страниц:</div>
                            <div class="fw-semibold" id="rangeCount">0</div>
                        </div>

                        <div class="d-flex justify-content-between mt-1">
                            <div class="text-muted">Сумма:</div>
                            <div class="fw-semibold" id="rangeTotal">0 сом</div>
                        </div>

                        <hr>

                        <form method="POST" action="{{ route('books.buy', $book) }}">
							@csrf

							<input type="hidden" name="from" id="formFrom">
							<input type="hidden" name="to" id="formTo">

							@guest
								<div class="mb-2">
									<input type="text" name="guest_name" class="form-control" placeholder="Ваше ФИО" required>
								</div>

								<div class="mb-2">
									<input type="email" name="guest_email" class="form-control" placeholder="Email для получения ссылки" required>
								</div>

								<div class="mb-2">
									<input type="text" name="guest_phone" class="form-control" placeholder="Телефон" required>
								</div>
							@endguest

							<button id="btnPayRange" class="btn btn-brand w-100" type="submit" disabled>
								Перейти к QR-оплате
							</button>
						</form>

                        <div class="text-muted small mt-2">
                            Ограничение: максимум <strong>15</strong> страниц за один заказ.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection