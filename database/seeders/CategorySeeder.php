<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'Научный журнал']);
        Category::create(['name' => 'Вестник']);
        Category::create(['name' => 'Книги']);
        Category::create(['name' => 'Авторефераты к диссертациям']);
    }
}