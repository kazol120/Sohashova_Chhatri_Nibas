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
        Schema::create('monthly_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_booking_history_id');
            $table->string('payment_month');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('trx_id')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('pending'); // pending, paid, rejected
            $table->string('received_by')->nullable();
            $table->timestamps();

            $table->foreign('room_booking_history_id')->references('id')->on('room_booking_histories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_payments');
    }
};
