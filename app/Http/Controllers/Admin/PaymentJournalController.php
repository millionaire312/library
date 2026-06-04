<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentJournalController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'items.book', 'payments'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('id', $q)
                        ->orWhere('guest_name', 'like', "%{$q}%")
                        ->orWhere('guest_email', 'like', "%{$q}%")
                        ->orWhere('guest_phone', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($userQuery) use ($q) {
                            $userQuery->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
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

        return view('admin.payments.index', compact('orders'));
    }
}