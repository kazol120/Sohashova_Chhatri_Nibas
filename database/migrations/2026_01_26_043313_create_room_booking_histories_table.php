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
        Schema::create('room_booking_histories', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->json('floor_number_room_number_roomprice')->nullable();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('nid')->nullable();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->decimal('daybytotalamount', 12, 2)->nullable()->default(0);
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('thana_id')->nullable();
            $table->string('pay_cash_in')->nullable();
            $table->string('pay_online')->nullable();
            $table->decimal('payment_amount_total', 12, 2)->nullable()->default(0);
            $table->date('check_in');
            $table->date('check_out');
            $table->date('today_check_out')->nullable();
            $table->string('status')->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_booking_histories');
    }
};
