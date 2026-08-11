<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meal_requests', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('date');
            $table->integer('total_days')->default(1)->after('end_date');
        });
    }

    public function down(): void
    {
        Schema::table('meal_requests', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'total_days']);
        });
    }
};
