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
            // Fields for individual customers
            $table->string('first_name')->nullable()->after('customer_type');
            $table->string('last_name')->nullable()->after('first_name');
            $table->date('date_of_birth')->nullable()->after('last_name');
            $table->string('nationality')->nullable()->after('date_of_birth');
            $table->string('email')->nullable()->after('phone_number');
            $table->text('residential_address')->nullable()->after('email');
            $table->string('passport_number')->nullable()->after('residential_address');
            $table->string('driving_license_number')->nullable()->after('passport_number');
            $table->date('license_expiry_date')->nullable()->after('driving_license_number');
            $table->string('license_issue_country')->nullable()->after('license_expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'date_of_birth',
                'nationality',
                'email',
                'residential_address',
                'passport_number',
                'driving_license_number',
                'license_expiry_date',
                'license_issue_country',
            ]);
        });
    }
};
