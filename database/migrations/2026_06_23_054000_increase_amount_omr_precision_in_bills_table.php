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
            $table->decimal('amount', 15, 3)->change();
            $table->decimal('amount_usd', 15, 3)->nullable()->change();
            $table->decimal('amount_omr', 15, 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
            $table->decimal('amount_usd', 10, 2)->nullable()->change();
            $table->decimal('amount_omr', 10, 2)->nullable()->change();
        });
    }
};
