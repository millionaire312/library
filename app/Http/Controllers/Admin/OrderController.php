<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'items.book', 'payments'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->email, function ($query, $email) {
                $query->whereHas('user', function ($q) use ($email) {
                    $q->where('email', 'like', '%' . $email . '%');
                });
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }
}