<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('room_booking_histories', 'blood_group')) {
            Schema::table('room_booking_histories', function (Blueprint $table) {
                $table->string('blood_group', 20)->nullable()->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('room_booking_histories', 'blood_group')) {
            Schema::table('room_booking_histories', function (Blueprint $table) {
                $table->dropColumn('blood_group');
            });
        }
    }
};
