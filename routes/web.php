<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use setasign\Fpdi\Fpdi;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Models\Category;
use App\Http\Controllers\Admin\PaymentJournalController;

use App\Services\FinikService;


Route::get('/', function (Request $request) {
    $categories = Category::with(['children' => function ($query) {
        $query->orderBy('sort_order')->orderBy('name');
		}])
		->whereNull('parent_id')
		->orderBy('sort_order')
		->orderBy('name')
		->get();

    $books = Book::with('category')
        ->when($request->q, function ($query, $q) {
            $query->where('title', 'like', '%' . $q . '%');
        })
        ->when($request->author, function ($query, $author) {
            $query->where('author', 'like', '%' . $author . '%');
        })
        ->when($request->year_from, function ($query, $year) {
            $query->where('year', '>=', $year);
        })
        ->when($request->year_to, function ($query, $year) {
            $query->where('year', '<=', $year);
        })
        ->when($request->category_id, function ($query, $categoryId) {
			$category = Category::with('children')->find($categoryId);

			if ($category) {
				$ids = $category->children->pluck('id')->toArray();
				$ids[] = $category->id;

				$query->whereIn('category_id', $ids);
			}
		})
        ->when($request->sort === 'price_asc', function ($query) {
			$query->orderBy('price_per_page', 'asc');
		})
		->when($request->sort === 'price_desc', function ($query) {
			$query->orderBy('price_per_page', 'desc');
		})
		->when($request->sort === 'year_desc', function ($query) {
			$query->orderBy('year', 'desc');
		})
		->when($request->sort === 'year_asc', function ($query) {
			$query->orderBy('year', 'asc');
		})
		->when(!$request->sort, function ($query) {
			$query->latest();
		})
		->paginate(20)
        ->withQueryString();

		return view('catalog.index', [
			'title' => 'Электронная библиотека',
			'books' => $books,
			'categories' => $categories,
		]);
	})->name('home');

Route::get('/books/{book}', function (Book $book) {
		$book->load('category');

		return view('books.show', [
			'title' => $book->title,
			'book' => $book,
		]);
	})->name('books.show');

Route::get('/dashboard', function () {
		return view('dashboard');
	})->middleware(['auth'])->name('dashboard');

Route::post('/books/{book}/buy', function (Request $request, Book $book) {
		$from = (int) $request->input('from');
		$to = (int) $request->input('to');

		if ($from <= 0 || $to <= 0 || $to < $from) {
			return back()->with('error', 'Неверный диапазон страниц');
		}

		$count = ($to - $from) + 1;

		if ($count > 15) {
			return back()->with('error', 'Можно выбрать максимум 15 страниц');
		}

		if ($to > $book->pages) {
			return back()->with('error', 'Страницы выходят за пределы книги');
		}

		$price = ($book->is_discount && $book->discount_price)
			? $book->discount_price
			: $book->price_per_page;

		$total = $count * $price;

		if (!auth()->check()) {
			$request->validate([
				'guest_name' => ['required', 'string', 'max:255'],
				'guest_email' => ['required', 'email', 'max:255'],
				'guest_phone' => ['required', 'string', 'max:50'],
			]);
		}

		$order = Order::create([
			'user_id' => auth()->id(),
			'guest_name' => auth()->check() ? null : $request->guest_name,
			'guest_email' => auth()->check() ? null : $request->guest_email,
			'guest_phone' => auth()->check() ? null : $request->guest_phone,
			'access_token' => Str::random(40),
			'payment_method' => 'qr_finik',
			'total' => $total,
			'status' => 'pending',
		]);

		OrderItem::create([
			'order_id' => $order->id,
			'book_id' => $book->id,
			'page_from' => $from,
			'page_to' => $to,
			'pages_count' => $count,
			'price_per_page' => $price,
			'total' => $total,
		]);

		return redirect()->route('orders.qr-pay', [
			'order' => $order,
			'token' => $order->access_token,
		]);
	})->name('books.buy');

Route::get('/my-orders', function () {
		$orders = Auth::user()
			->orders()
			->with('items.book', 'payments')
			->latest()
			->get();

		return view('cabinet.orders', compact('orders'));
	})->middleware('auth')->name('orders');

Route::post('/orders/{order}/pay', function (Order $order) {
		if ($order->user_id !== Auth::id()) {
			abort(403);
		}

		if ($order->status === 'paid') {
			return back()->with('success', 'Заказ уже оплачен.');
		}

		$order->update([
			'status' => 'paid',
		]);

		Payment::create([
			'order_id' => $order->id,
			'amount' => $order->total,
			'provider' => 'demo',
			'status' => 'paid',
			'transaction_id' => 'DEMO-' . time() . '-' . $order->id,
		]);

		return back()->with('success', 'Оплата прошла успешно.');
	})->middleware('auth')->name('orders.pay');

Route::post('/orders/{order}/finik-pay/{token}', function (Order $order, string $token, FinikService $finikService) {

\Log::info('FINIK PAY BUTTON CLICKED', [
    'order_id' => $order->id,
    'order_status' => $order->status,
    'token_ok' => $order->access_token && hash_equals($order->access_token, $token),
]);


    if (!$order->access_token || !hash_equals($order->access_token, $token)) {
        abort(403);
    }

    if ($order->status === 'paid') {
        return redirect()->route('orders.qr-pay', [$order, $token])
            ->with('success', 'Заказ уже оплачен.');
    }

    $result = $finikService->createPayment($order, $token);

    Payment::create([
        'order_id' => $order->id,
        'amount' => $order->total,
        'provider' => 'finik',
        'provider_payment_id' => $result['payment_id'],
        'payment_url' => $result['payment_url'],
        'status' => 'pending',
        'transaction_id' => $result['payment_id'],
        'request_payload' => $result['request_payload'],
        'response_payload' => $result['response_payload'],
    ]);

    if (!$result['payment_url']) {
        return back()->with('error', 'Finik не вернул ссылку на оплату.');
    }

    return redirect()->away($result['payment_url']);
})->name('orders.finik-pay');

Route::post('/webhooks/finik', function (Request $request) {

\Log::info('FINIK WEBHOOK RECEIVED', [
    'headers' => $request->headers->all(),
    'body' => $request->all(),
    'raw' => $request->getContent(),
]);

    $payload = $request->all();

    $orderId = data_get($payload, 'fields.order_id')
        ?? data_get($payload, 'data.additionalData.0.value');

    $status = strtolower((string) data_get($payload, 'status'));

    $transactionId = data_get($payload, 'transactionId')
        ?? data_get($payload, 'id')
        ?? data_get($payload, 'fields.paymentId');

    if (!$orderId) {
        return response()->json(['error' => 'order_id missing'], 400);
    }

    $order = \App\Models\Order::find($orderId);

    if (!$order) {
        return response()->json(['error' => 'order not found'], 404);
    }

    if (in_array($status, ['succeeded', 'success', 'paid'], true)) {
        $order->update([
            'status' => 'paid',
        ]);

        \App\Models\Payment::updateOrCreate(
            [
                'order_id' => $order->id,
                'provider' => 'finik',
                'transaction_id' => $transactionId,
            ],
            [
                'amount' => $order->total,
                'status' => 'paid',
                'provider_payment_id' => data_get($payload, 'fields.paymentId'),
                'response_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'paid_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    if (in_array($status, ['failed', 'failure', 'declined', 'cancelled'], true)) {
        \App\Models\Payment::updateOrCreate(
            [
                'order_id' => $order->id,
                'provider' => 'finik',
                'transaction_id' => $transactionId,
            ],
            [
                'amount' => $order->total,
                'status' => 'failed',
                'provider_payment_id' => data_get($payload, 'fields.paymentId'),
                'response_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]
        );

        return response()->json(['ok' => true]);
    }

    return response()->json([
        'ok' => true,
        'status' => $status,
    ]);
})->name('finik.webhook');

Route::get('/orders/{order}/download/{item}', function (\App\Models\Order $order, \App\Models\OrderItem $item) {

		if (!auth()->check()) {
			abort(403);
		}

		if ($order->user_id !== auth()->id()) {
			abort(403, 'Это не ваш заказ');
		}

		if ($item->order_id !== $order->id) {
			abort(403, 'Этот файл не относится к заказу');
		}

		if ($order->status !== 'paid') {
			abort(403, 'Заказ не оплачен');
		}

		if ($item->downloads >= 5) {
			abort(403, 'Лимит скачиваний превышен');
		}

		$book = $item->book;

		if (!$book || !$book->pdf) {
			abort(404, 'Файл книги не указан');
		}

		$filePath = storage_path('app/public/' . $book->pdf);

		if (!file_exists($filePath)) {
			abort(404, 'PDF файл не найден');
		}

		$pdf = new \setasign\Fpdi\Fpdi();

		$pageCount = $pdf->setSourceFile($filePath);

		for ($page = $item->page_from; $page <= $item->page_to; $page++) {
			if ($page > $pageCount) {
				break;
			}

			$tpl = $pdf->importPage($page);
			$size = $pdf->getTemplateSize($tpl);

			$orientation = $size['width'] > $size['height'] ? 'L' : 'P';

			$pdf->AddPage($orientation, [$size['width'], $size['height']]);
			$pdf->useTemplate($tpl);

			// Водяной знак
			$pdf->SetFont('Arial', '', 10);
			$pdf->SetTextColor(180, 180, 180);
			$pdf->SetXY(10, $size['height'] - 10);
			$pdf->Write(0, 'Downloaded by: ' . auth()->user()->email);
		}

		$item->increment('downloads');
		\App\Models\ActivityLog::create([
			'user_id' => auth()->id(),
			'action' => 'downloaded',
			'model' => 'OrderItem',
			'model_id' => $item->id,
			'title' => $book->title,
			'ip' => request()->ip(),
			'description' => 'Скачаны страницы ' . $item->page_from . '–' . $item->page_to,
		]);

		return response($pdf->Output('S'))
			->header('Content-Type', 'application/pdf')
			->header(
				'Content-Disposition',
				'attachment; filename="book-'.$book->id.'-pages-'.$item->page_from.'-'.$item->page_to.'.pdf"'
			);

	})->middleware('auth')->name('orders.download');
	
Route::middleware(['auth', 'editor'])->prefix('admin')->group(function () {
		Route::get('/books', [AdminBookController::class, 'index'])->name('admin.books.index');
		Route::get('/books/create', [AdminBookController::class, 'create'])->name('admin.books.create');
		Route::post('/books', [AdminBookController::class, 'store'])->name('admin.books.store');
		Route::get('/books/{book}/edit', [AdminBookController::class, 'edit'])->name('admin.books.edit');
		Route::put('/books/{book}', [AdminBookController::class, 'update'])->name('admin.books.update');
		Route::delete('/books/{book}', [AdminBookController::class, 'destroy'])->name('admin.books.destroy');

		Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
		Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('admin.categories.create');
		Route::post('/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
		Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('admin.categories.edit');
		Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
		Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
	});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
		Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
		Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
		Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
		Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
		Route::get('/activity-logs', [ActivityLogController::class, 'index'])
			->name('admin.activity-logs.index');
		Route::get('/payments', [PaymentJournalController::class, 'index'])
			->name('admin.payments.index');
	});
	
Route::get('/my-payments', function () {
		$payments = auth()->user()
			->payments()
			->with('order')
			->latest()
			->get();

		return view('cabinet.payments', compact('payments'));
	})->middleware('auth')->name('payments');
	
Route::get('/books/{book}/preview', function (Book $book) {
		if (!$book->pdf) {
			abort(404, 'PDF не указан');
		}

		$filePath = storage_path('app/public/' . $book->pdf);

		if (!file_exists($filePath)) {
			abort(404, 'PDF не найден');
		}

		$pdf = new \setasign\Fpdi\Fpdi();

		$pageCount = $pdf->setSourceFile($filePath);
		$limit = min(10, $pageCount);

		for ($page = 1; $page <= $limit; $page++) {
			$templateId = $pdf->importPage($page);
			$size = $pdf->getTemplateSize($templateId);

			$pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
			$pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height'], true);
		}

		return response($pdf->Output('S'))
			->header('Content-Type', 'application/pdf')
			->header('Content-Disposition', 'inline; filename="preview-'.$book->id.'.pdf"');

	})->name('books.preview');

Route::get('/orders/{order}/qr-pay/{token}', function (Order $order, string $token) {

    if (!$order->access_token || !hash_equals($order->access_token, $token)) {
        abort(403);
    }

    $order->load(['items.book', 'payments']);

    $finikPayment = $order->payments()
        ->where('provider', 'finik')
        ->latest()
        ->first();

    if ($order->status !== 'paid' && !$finikPayment) {

        $finikService = app(\App\Services\FinikService::class);

        $result = $finikService->createPayment($order, $token);

        Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total,
            'provider' => 'finik',
            'provider_payment_id' => $result['payment_id'],
            'payment_url' => $result['payment_url'],
            'status' => 'pending',
            'transaction_id' => $result['payment_id'],
            'request_payload' => $result['request_payload'],
            'response_payload' => $result['response_payload'],
        ]);

        $finikPayment = $order->payments()
            ->where('provider', 'finik')
            ->latest()
            ->first();
    }

    return view('orders.qr-pay', [
        'order' => $order,
        'finikPayment' => $finikPayment,
    ]);

})->name('orders.qr-pay');

/*Route::get('/orders/{order}/qr-pay/{token}', function (Order $order, string $token) {
		
		if (!$order->access_token || !hash_equals($order->access_token, $token)) {
			abort(403);
		}

		if (auth()->check() && $order->user_id && $order->user_id !== auth()->id()) {
			abort(403);
		}

		$order->load('items.book');

		return view('orders.qr-pay', compact('order'));
	})->name('orders.qr-pay');*/

Route::post('/orders/{order}/qr-confirm/{token}', function (Order $order, string $token) {
		
		if (!$order->access_token || !hash_equals($order->access_token, $token)) {
			abort(403);
		}

		if ($order->status !== 'paid') {
			$order->update([
				'status' => 'paid',
			]);

			Payment::create([
				'order_id' => $order->id,
				'amount' => $order->total,
				'provider' => 'qr_demo',
				'status' => 'paid',
				'transaction_id' => 'QR-DEMO-' . time() . '-' . $order->id,
			]);
		}

		return redirect()->route('orders.qr-pay', [$order, $token])
			->with('success', 'Оплата подтверждена.');
	})->name('orders.qr-confirm');

Route::get('/orders/{order}/guest-downloads/{token}', function (Order $order, string $token) {
		
		if (!$order->access_token || !hash_equals($order->access_token, $token)) {
			abort(403);
		}
		
		if ($order->created_at->lt(now()->subDays(7))) {
			abort(403, 'Срок действия ссылки истёк');
		}
		
		if ($order->status !== 'paid') {
			abort(403, 'Заказ не оплачен');
		}

		$order->load('items.book');

		return view('orders.guest-downloads', compact('order'));
	})->name('orders.guest-downloads');
	
Route::get('/orders/{order}/guest-download/{item}/{token}', function (Order $order, OrderItem $item, string $token) {
		
		if (!$order->access_token || !hash_equals($order->access_token, $token)) {
			abort(403);
		}
		
		if ($order->created_at->lt(now()->subDays(7))) {
			abort(403, 'Срок действия ссылки истёк');
		}
		
		if ($item->order_id !== $order->id) {
			abort(403, 'Этот файл не относится к заказу');
		}

		if ($order->status !== 'paid') {
			abort(403, 'Заказ не оплачен');
		}

		if ($item->downloads >= 5) {
			abort(403, 'Лимит скачиваний превышен');
		}

		$book = $item->book;

		if (!$book || !$book->pdf) {
			abort(404, 'PDF не указан');
		}
		
		if (str_contains($book->pdf, '..')) {
			abort(403);
		}
		
		$filePath = storage_path('app/public/' . $book->pdf);

		if (!file_exists($filePath)) {
			abort(404, 'PDF не найден');
		}

		$pdf = new \setasign\Fpdi\Fpdi();
		$pageCount = $pdf->setSourceFile($filePath);

		for ($page = $item->page_from; $page <= $item->page_to; $page++) {
			if ($page > $pageCount) {
				break;
			}

			$tpl = $pdf->importPage($page);
			$size = $pdf->getTemplateSize($tpl);

			$pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
			$pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height'], true);

			$pdf->SetFont('Arial', '', 10);
			$pdf->SetTextColor(180, 180, 180);
			$pdf->SetXY(10, $size['height'] - 10);
			$pdf->Cell(0, 5, 'Order #' . $order->id . ' / ' . ($order->guest_email ?? optional($order->user)->email), 0, 0, 'L');
		}

		$item->increment('downloads');

		return response($pdf->Output('S'))
			->header('Content-Type', 'application/pdf')
			->header('Content-Disposition', 'attachment; filename="order-'.$order->id.'-pages-'.$item->page_from.'-'.$item->page_to.'.pdf"');

	})->name('orders.guest-download');
	
require __DIR__.'/auth.php';
