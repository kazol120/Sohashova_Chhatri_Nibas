<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_booking_history_id');
            $table->unsignedBigInteger('monthly_payment_id')->nullable();
            $table->string('payment_month')->nullable(); // e.g. 2026-08, 2026-09
            $table->decimal('rent_paid', 10, 2)->default(0);
            $table->decimal('carried_due_paid', 10, 2)->default(0);
            $table->decimal('electricity_bill_paid', 10, 2)->default(0);
            $table->decimal('total_collected', 10, 2)->default(0);
            $table->string('payment_method')->default('Cash');
            $table->string('trx_id')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('received_by')->nullable();
            $table->timestamps();

            $table->foreign('room_booking_history_id')->references('id')->on('room_booking_histories')->onDelete('cascade');
            $table->foreign('monthly_payment_id')->references('id')->on('monthly_payments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_payment_transactions');
    }
};
