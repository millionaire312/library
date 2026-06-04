@extends('layouts.cabinet')

@section('cabinet_content')
<h3 class="mb-4">Профиль</h3>

<div class="card p-4">
    <div class="mb-2">
        <strong>Имя:</strong> {{ auth()->user()->name }}
    </div>

    <div class="mb-2">
        <strong>Email:</strong> {{ auth()->user()->email }}
    </div>

    <div class="mb-2">
        <strong>Роль:</strong> {{ auth()->user()->role }}
    </div>
</div>
@endsection
