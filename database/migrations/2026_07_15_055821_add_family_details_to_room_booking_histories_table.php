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
            $table->string('mother_nid')->nullable()->after('nid');
            $table->string('father_nid')->nullable()->after('mother_nid');
            $table->string('father_name')->nullable()->after('full_name');
            $table->string('mother_name')->nullable()->after('father_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->dropColumn(['mother_nid', 'father_nid', 'father_name', 'mother_name']);
        });
    }
};
