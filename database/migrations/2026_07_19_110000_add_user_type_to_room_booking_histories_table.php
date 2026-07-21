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
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->string('user_type')->default('student')->after('full_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
    }
};
