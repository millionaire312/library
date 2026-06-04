<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'year',
        'description',
        'cover',
		'pdf',
        'pages',
        'price_per_page',
        'is_discount',
        'discount_price',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}