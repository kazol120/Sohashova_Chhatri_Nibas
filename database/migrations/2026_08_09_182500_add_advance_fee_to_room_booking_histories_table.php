<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->decimal('advance_fee', 10, 2)->default(0.00)->after('development_fee');
        });
    }

    public function down(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->dropColumn('advance_fee');
        });
    }
};
