<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Library' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">📚 Library</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="topNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<li class="nav-item">
					<a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('home') }}">
						Каталог
					</a>
				</li>

				@foreach($menuCategories ?? [] as $category)
					@if($category->children->count())
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle {{ request('category_id') == $category->id ? 'active' : '' }}"
							   href="#"
							   role="button"
							   data-bs-toggle="dropdown"
							   aria-expanded="false">
								{{ $category->name }}
							</a>

							<ul class="dropdown-menu">
								<li>
									<a class="dropdown-item" href="{{ route('home', ['category_id' => $category->id]) }}">
										Все: {{ $category->name }}
									</a>
								</li>

								<li><hr class="dropdown-divider"></li>

								@foreach($category->children as $child)
									<li>
										<a class="dropdown-item {{ request('category_id') == $child->id ? 'active' : '' }}"
										   href="{{ route('home', ['category_id' => $child->id]) }}">
											{{ $child->name }}
										</a>
									</li>
								@endforeach
							</ul>
						</li>
					@else
						<li class="nav-item">
							<a class="nav-link {{ request('category_id') == $category->id ? 'active' : '' }}"
							   href="{{ route('home', ['category_id' => $category->id]) }}">
								{{ $category->name }}
							</a>
						</li>
					@endif
				@endforeach
			</ul>

            <div class="d-flex gap-2">
				@guest
					<a class="btn btn-outline-brand" href="{{ route('login') }}">Вход</a>
					<a class="btn btn-brand" href="{{ route('register') }}">Регистрация</a>
				@endguest

				@auth
					<a class="btn btn-outline-brand" href="{{ url('/dashboard') }}">Кабинет</a>

					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button type="submit" class="btn btn-brand">Выход</button>
					</form>
				@endauth
			</div>
        </div>
    </div>
</nav>

<main class="container my-4">
    @yield('content')
</main>

<footer class="border-top bg-white">
    <div class="container py-4 footer d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>© {{ date('Y') }} Library</div>
        <div class="text-muted">Электронная библиотека</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>