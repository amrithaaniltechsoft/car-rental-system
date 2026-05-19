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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->enum('type', ['fleet', 'general']);
            $table->string('category'); // petrol, maintenance, office, etc.
            $table->decimal('amount', 10, 2); // OMR currency
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('set null'); // for fleet expenses
            $table->string('reference_number')->nullable(); // receipt/bill number
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
