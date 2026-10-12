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
        Schema::table('monthly_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_payments', 'electricity_bill')) {
                $table->decimal('electricity_bill', 10, 2)->default(0.00)->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_payments', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_payments', 'electricity_bill')) {
                $table->dropColumn('electricity_bill');
            }
        });
    }
};
