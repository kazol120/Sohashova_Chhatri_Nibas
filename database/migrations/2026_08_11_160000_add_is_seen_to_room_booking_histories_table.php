<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('room_booking_histories', 'is_seen')) {
            Schema::table('room_booking_histories', function (Blueprint $table) {
                $table->tinyInteger('is_seen')->default(0)->after('status');
            });

            // Mark all existing old records as seen (1) so only new future bookings trigger notifications
            DB::table('room_booking_histories')->update(['is_seen' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('room_booking_histories', 'is_seen')) {
            Schema::table('room_booking_histories', function (Blueprint $table) {
                $table->dropColumn('is_seen');
            });
        }
    }
};
