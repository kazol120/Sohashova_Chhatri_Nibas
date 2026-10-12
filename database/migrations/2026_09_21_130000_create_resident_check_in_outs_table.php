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
        Schema::create('resident_check_in_outs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('room_booking_history_id')->index();
            $table->string('resident_name');
            $table->string('phone');
            $table->string('floor_room_seat')->nullable();
            $table->dateTime('check_out_date');
            $table->date('expected_return_date')->nullable();
            $table->dateTime('check_in_date')->nullable();
            $table->string('leave_reason')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0: checked_out / outside, 1: checked_in / returned, 2: cancelled');
            $table->boolean('is_seen_by_admin')->default(false);
            $table->string('created_by')->default('resident')->comment('resident or admin');
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->foreign('room_booking_history_id')
                  ->references('id')
                  ->on('room_booking_histories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resident_check_in_outs');
    }
};
