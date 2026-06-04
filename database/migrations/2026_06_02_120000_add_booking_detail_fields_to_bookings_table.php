<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('booking_date')->nullable()->after('booking_id');
            $table->datetime('pickup_datetime')->nullable()->after('booking_date');
            $table->datetime('return_datetime')->nullable()->after('pickup_datetime');
            $table->string('rental_duration')->nullable()->after('return_datetime');
            $table->string('pickup_location')->nullable()->after('rental_duration');
            $table->string('return_location')->nullable()->after('pickup_location');
        });

        // Update status enum to add 'completed'
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_date', 'pickup_datetime', 'return_datetime', 'rental_duration', 'pickup_location', 'return_location']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('pending','confirmed','on_hold','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
