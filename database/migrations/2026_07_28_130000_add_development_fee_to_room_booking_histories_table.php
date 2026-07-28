<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->decimal('development_fee', 10, 2)->default(0.00)->after('monthly_amount');
        });
    }

    public function down(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->dropColumn('development_fee');
        });
    }
};
