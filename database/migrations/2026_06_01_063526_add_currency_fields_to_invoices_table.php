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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('total_usd', 10, 2)->nullable()->after('total');
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('total_usd');
            $table->decimal('total_omr', 10, 2)->nullable()->after('exchange_rate');
            $table->decimal('subtotal_usd', 10, 2)->nullable()->after('subtotal');
            $table->decimal('vat_amount_usd', 10, 2)->nullable()->after('vat_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'total_usd',
                'exchange_rate',
                'total_omr',
                'subtotal_usd',
                'vat_amount_usd',
            ]);
        });
    }
};
