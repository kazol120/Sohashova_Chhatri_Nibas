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
            if (Schema::hasColumn('room_booking_histories', 'payment_amount_total')) {
                $table->renameColumn('payment_amount_total', 'monthly_amount');
            } else if (!Schema::hasColumn('room_booking_histories', 'monthly_amount')) {
                $table->decimal('monthly_amount', 12, 2)->nullable()->default(0);
            }

            if (Schema::hasColumn('room_booking_histories', 'daybytotalamount')) {
                $table->dropColumn('daybytotalamount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_booking_histories', function (Blueprint $table) {
            if (Schema::hasColumn('room_booking_histories', 'monthly_amount')) {
                $table->renameColumn('monthly_amount', 'payment_amount_total');
            }
            if (!Schema::hasColumn('room_booking_histories', 'daybytotalamount')) {
                $table->decimal('daybytotalamount', 12, 2)->nullable()->default(0);
            }
        });
    }
};
