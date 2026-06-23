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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider_payment_id')->nullable()->after('provider');
	    $table->text('payment_url')->nullable()->after('provider_payment_id');
            $table->longText('request_payload')->nullable()->after('transaction_id');
            $table->longText('response_payload')->nullable()->after('request_payload');
            $table->timestamp('paid_at')->nullable()->after('response_payload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
	    $table->dropColumn([
            	'provider_payment_id',
            	'payment_url',
            	'request_payload',
            	'response_payload',
            	'paid_at',
             ]);
        });
    }
};
