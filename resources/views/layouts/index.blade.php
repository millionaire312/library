@extends('layouts.app')

@section('content')
<section class="hero p-4 p-lg-5 mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-lg-6">
            <h1 class="mb-2">Каталог книг</h1>
            <div class="small-muted">
                Гость может просматривать книги и фильтровать по автору, названию и годам.
            </div>
        </div>

        <div class="col-lg-6">
            <form class="row g-2" method="GET" action="{{ url('/') }}">
                <div class="col-12">
                    <input class="form-control" type="search" name="q" placeholder="Поиск по названию книги…">
                </div>
                <div class="col-12 col-md-6">
                    <input class="form-control" type="text" name="author" placeholder="Автор">
                </div>
                <div class="col-6 col-md-3">
                    <input id="yearFrom" class="form-control" type="number" name="year_from" placeholder="Год от">
                </div>
                <div class="col-6 col-md-3">
                    <input id="yearTo" class="form-control" type="number" name="year_to" placeholder="Год до">
                </div>

                <div class="col-12 col-md-6">
                    <select id="yearPreset" class="form-select">
                        <option value="">Быстрый выбор…</option>
                        <option value="last5">Последние 5 лет</option>
                        <option value="last10">Последние 10 лет</option>
                        <option value="classic">Классика (1900–1999)</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 d-flex gap-2">
                    <a class="btn btn-light w-100" href="{{ url('/') }}">Сброс</a>
                    <button class="btn btn-brand w-100" type="submit">Применить</button>
                </div>
            </form>
        </div>
    </div>
</section>

<section class="rubric-tabs mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex flex-wrap gap-2">
            <a class="rubric-tab active" href="#">Все</a>
            <a class="rubric-tab" href="#">Научный журнал</a>
            <a class="rubric-tab" href="#">Вестник</a>
            <a class="rubric-tab" href="#">Книги</a>
            <a class="rubric-tab" href="#">Авторефераты к диссертациям</a>
        </div>

        <div class="text-muted small">
            Найдено: <strong>{{ $books->count() }}</strong>
        </div>
    </div>
</section>

<section class="row g-3">
    @forelse($books as $book)
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card card-hover p-3">
                <div class="card-media">
                    @if($book->is_discount && $book->price_per_page > 0 && $book->discount_price)
                        <span class="badge-discount">
                            -{{ round((($book->price_per_page - $book->discount_price) / $book->price_per_page) * 100) }}%
                        </span>
                    @endif

                    <img class="cover-sm" src="https://picsum.photos/seed/{{ $book->id }}/600/400" alt="cover">
                </div>

                <div class="mt-3 d-flex align-items-center justify-content-between gap-2">
                    <span class="tag">
                        <span class="tag-dot"></span>
                        {{ $book->category->name ?? 'Без рубрики' }}
                    </span>

                    @if($book->is_discount)
                        <span class="badge badge-soft">Акция</span>
                    @endif
                </div>

                <div class="mt-2">
                    <div class="fw-semibold">{{ $book->title }}</div>
                    <div class="text-muted small">{{ $book->author }} • {{ $book->year }}</div>

                    <div class="mt-2 small text-muted">
                        Страниц: {{ $book->pages }}
                    </div>

                    <div class="mt-2">
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

                    <div class="mt-3">
                        <a class="btn btn-outline-brand w-100" href="{{ route('books.show', $book) }}">Подробнее</a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-light border">
                Книги пока не добавлены.
            </div>
        </div>
    @endforelse
</section>
@endsection