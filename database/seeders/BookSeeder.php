<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\Category;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cat1 = Category::where('name', 'Научный журнал')->first();
        $cat2 = Category::where('name', 'Книги')->first();
        $cat3 = Category::where('name', 'Вестник')->first();
        $cat4 = Category::where('name', 'Авторефераты к диссертациям')->first();

        Book::create([
            'title' => 'Математика для инженеров',
            'author' => 'И. Иванов',
            'year' => 2018,
            'description' => 'Учебное пособие',
            'cover' => null,
            'pages' => 420,
            'price_per_page' => 30,
            'is_discount' => true,
            'discount_price' => 24,
            'category_id' => $cat1?->id,
        ]);

        Book::create([
            'title' => 'Белый пароход',
            'author' => 'Ч. Айтматов',
            'year' => 1970,
            'description' => 'Художественная книга',
            'cover' => null,
            'pages' => 180,
            'price_per_page' => 30,
            'is_discount' => false,
            'discount_price' => null,
            'category_id' => $cat2?->id,
        ]);

        Book::create([
            'title' => 'История Кыргызстана',
            'author' => 'Сборник',
            'year' => 2005,
            'description' => 'Исторический вестник',
            'cover' => null,
            'pages' => 260,
            'price_per_page' => 0,
            'is_discount' => false,
            'discount_price' => null,
            'category_id' => $cat3?->id,
        ]);

        Book::create([
            'title' => 'PHP Практика',
            'author' => 'A. Developer',
            'year' => 2024,
            'description' => 'Автореферат',
            'cover' => null,
            'pages' => 310,
            'price_per_page' => 30,
            'is_discount' => false,
            'discount_price' => null,
            'category_id' => $cat4?->id,
        ]);
    }
}