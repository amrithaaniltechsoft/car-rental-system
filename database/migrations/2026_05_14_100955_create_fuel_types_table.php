<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fuel_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('fuel_types')->insert([
            ['name' => 'petrol', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'diesel', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'electric', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'hybrid', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_types');
    }
};
