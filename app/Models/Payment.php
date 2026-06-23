<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'provider',
        'status',
        'transaction_id',
	'provider_payment_id',
	'payment_url',
	'request_payload',
	'response_payload',
	'paid_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
