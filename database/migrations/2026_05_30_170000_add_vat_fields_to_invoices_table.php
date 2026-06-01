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
            if (!Schema::hasColumn('invoices', 'rate')) {
                $table->decimal('rate', 8, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('invoices', 'vat')) {
                $table->decimal('vat', 8, 2)->nullable()->after('rate');
            }
            if (!Schema::hasColumn('invoices', 'vat_amount')) {
                $table->decimal('vat_amount', 10, 2)->nullable()->after('vat');
            }
            if (!Schema::hasColumn('invoices', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->nullable()->after('vat_amount');
            }
            if (!Schema::hasColumn('invoices', 'total')) {
                $table->decimal('total', 10, 2)->nullable()->after('subtotal');
            }
            if (!Schema::hasColumn('invoices', 'due_date')) {
                $table->date('due_date')->nullable()->after('invoice_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['rate', 'vat', 'vat_amount', 'subtotal', 'total', 'due_date']);
        });
    }
};
