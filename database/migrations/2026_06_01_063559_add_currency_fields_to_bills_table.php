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
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('amount_usd', 10, 2)->nullable()->after('amount');
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('amount_usd');
            $table->decimal('amount_omr', 10, 2)->nullable()->after('exchange_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['amount_usd', 'exchange_rate', 'amount_omr']);
        });
    }
};
