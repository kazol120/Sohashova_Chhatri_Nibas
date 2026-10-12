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
        Schema::table('staff_salary_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_salary_payments', 'advance_deducted')) {
                $table->decimal('advance_deducted', 12, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('staff_salary_payments', 'gross_salary')) {
                $table->decimal('gross_salary', 12, 2)->nullable()->after('advance_deducted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_salary_payments', function (Blueprint $table) {
            if (Schema::hasColumn('staff_salary_payments', 'advance_deducted')) {
                $table->dropColumn('advance_deducted');
            }
            if (Schema::hasColumn('staff_salary_payments', 'gross_salary')) {
                $table->dropColumn('gross_salary');
            }
        });
    }
};
