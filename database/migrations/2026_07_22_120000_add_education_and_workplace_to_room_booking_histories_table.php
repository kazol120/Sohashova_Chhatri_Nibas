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
            $table->string('institution_name')->nullable()->after('user_type');
            $table->string('education_level')->nullable()->after('institution_name');
            $table->string('education_class')->nullable()->after('education_level');
            $table->string('workplace_name')->nullable()->after('education_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            $table->dropColumn([
                'institution_name',
                'education_level',
                'education_class',
                'workplace_name'
            ]);
        });
    }
};
