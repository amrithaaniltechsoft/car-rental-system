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
            if (! Schema::hasColumn('invoices', 'rate_type')) {
                $table->string('rate_type')->nullable()->after('total');
            }
            if (! Schema::hasColumn('invoices', 'extra_kms_charges')) {
                $table->decimal('extra_kms_charges', 10, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('invoices', 'security_deposit')) {
                $table->decimal('security_deposit', 10, 2)->default(0)->after('extra_kms_charges');
            }
            if (! Schema::hasColumn('invoices', 'insurance_fee')) {
                $table->decimal('insurance_fee', 10, 2)->default(0)->after('security_deposit');
            }
            if (! Schema::hasColumn('invoices', 'additional_driver_fee')) {
                $table->decimal('additional_driver_fee', 10, 2)->default(0)->after('insurance_fee');
            }
            if (! Schema::hasColumn('invoices', 'delivery_charge')) {
                $table->decimal('delivery_charge', 10, 2)->default(0)->after('additional_driver_fee');
            }
            if (! Schema::hasColumn('invoices', 'fuel_charge')) {
                $table->decimal('fuel_charge', 10, 2)->default(0)->after('delivery_charge');
            }
            if (! Schema::hasColumn('invoices', 'gps_charges')) {
                $table->decimal('gps_charges', 10, 2)->default(0)->after('fuel_charge');
            }
            if (! Schema::hasColumn('invoices', 'salik_toll_charges')) {
                $table->decimal('salik_toll_charges', 10, 2)->default(0)->after('gps_charges');
            }
            if (! Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('salik_toll_charges');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $columns = [
                'rate_type', 'extra_kms_charges', 'security_deposit', 'insurance_fee',
                'additional_driver_fee', 'delivery_charge', 'fuel_charge', 'gps_charges',
                'salik_toll_charges', 'discount_amount',
            ];
            $droppable = array_filter($columns, fn ($col) => Schema::hasColumn('invoices', $col));
            if (! empty($droppable)) {
                $table->dropColumn($droppable);
            }
        });
    }
};
