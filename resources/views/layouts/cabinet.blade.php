@extends('layouts.app')

@section('content')
<div class="row g-3">
    <div class="col-lg-3">
        <div class="sidebar">
            <div class="fw-semibold mb-2">Личный кабинет</div>

            <nav class="nav flex-column gap-1">
                <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
                    Профиль
                </a>

                <a class="nav-link {{ request()->routeIs('orders') ? 'active' : '' }}" href="{{ route('orders') }}">
                    Мои покупки
                </a>
				
				<a class="nav-link {{ request()->routeIs('payments') ? 'active' : '' }}" href="{{ route('payments') }}">
					История платежей
				</a>

                @auth
                    @if(in_array(auth()->user()->role, ['admin', 'editor']))
                        <div class="mt-3 mb-1 text-muted small">Управление контентом</div>

                        <a class="nav-link {{ request()->routeIs('admin.books.*') ? 'active' : '' }}" href="{{ route('admin.books.index') }}">
                            Книги
                        </a>

                        <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                            Рубрики
                        </a>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <div class="mt-3 mb-1 text-muted small">Администрирование</div>

                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            Пользователи
                        </a>

                        <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                            Заказы
                        </a>

                        <a class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}" href="{{ route('admin.activity-logs.index') }}">
                            Журнал действий
                        </a>
						<a class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"
						   href="{{ route('admin.payments.index') }}">
							Журнал платежей
						</a>
                    @endif
                @endauth
            </nav>
        </div>
    </div>

    <div class="col-lg-9">
        @yield('cabinet_content')
    </div>
</div>
@endsection