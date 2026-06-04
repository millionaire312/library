<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
	{
		Schema::create('books', function (Blueprint $table) {
			$table->id();
			$table->string('title');
			$table->string('author');
			$table->integer('year')->nullable();
			$table->text('description')->nullable();
			$table->string('cover')->nullable();
			$table->integer('pages')->default(0);

			$table->decimal('price_per_page', 8, 2)->default(0);
			$table->boolean('is_discount')->default(false);
			$table->decimal('discount_price', 8, 2)->nullable();

			$table->foreignId('category_id')->constrained()->cascadeOnDelete();

			$table->timestamps();
		});
	}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
