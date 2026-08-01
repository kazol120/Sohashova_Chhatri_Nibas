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
        Schema::create('room_change_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_booking_history_id')->index();
            $table->string('resident_name');
            $table->string('phone')->nullable();
            $table->string('old_floor')->nullable();
            $table->string('old_room_seat')->nullable();
            $table->string('new_floor')->nullable();
            $table->string('new_room_seat')->nullable();
            $table->decimal('old_monthly_amount', 10, 2)->default(0);
            $table->decimal('new_monthly_amount', 10, 2)->default(0);
            $table->decimal('fee_amount', 10, 2)->default(500.00);
            $table->string('payment_method')->default('Cash');
            $table->string('payment_status')->default('paid');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_change_histories');
    }
};
