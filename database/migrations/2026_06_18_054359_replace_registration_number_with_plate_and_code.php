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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique(['registration_number']);
            $table->dropColumn('registration_number');
            $table->string('number_plate')->unique()->after('type');
            $table->string('number_code')->after('number_plate');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['number_plate', 'number_code']);
            $table->string('registration_number')->unique()->after('type');
        });
    }
};
