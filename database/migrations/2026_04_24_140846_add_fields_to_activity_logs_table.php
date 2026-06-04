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
		Schema::table('activity_logs', function (Blueprint $table) {
			$table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
			$table->string('action')->after('user_id');
			$table->string('model')->after('action');
			$table->unsignedBigInteger('model_id')->nullable()->after('model');
			$table->string('title')->nullable()->after('model_id');
			$table->string('ip')->nullable()->after('title');
			$table->text('description')->nullable()->after('ip');
		});
	}


    /**
     * Reverse the migrations.
     */
    public function down(): void
	{
		Schema::table('activity_logs', function (Blueprint $table) {
			$table->dropConstrainedForeignId('user_id');
			$table->dropColumn([
				'action',
				'model',
				'model_id',
				'title',
				'ip',
				'description',
			]);
		});
	}
};
