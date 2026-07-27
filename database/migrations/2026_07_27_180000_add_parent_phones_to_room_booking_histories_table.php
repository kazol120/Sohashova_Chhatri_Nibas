<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->string('father_phone', 20)->nullable()->after('father_nid');
            $table->string('mother_phone', 20)->nullable()->after('mother_nid');
        });
    }

    public function down(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->dropColumn(['father_phone', 'mother_phone']);
        });
    }
};
