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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_type')->default('individual')->after('id');
            $table->string('company_name')->nullable()->after('name');
            $table->string('company_registration_id')->nullable()->after('id_card_number');
            $table->string('id_card_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('id_card_number')->nullable(false)->change();
            $table->dropColumn(['customer_type', 'company_name', 'company_registration_id']);
        });
    }
};
