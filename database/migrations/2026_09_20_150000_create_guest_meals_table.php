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
        Schema::create('guest_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->date('date');
            $table->string('guest_name')->comment('পিতা, মাতা, ভাই, বোন বা নাম');
            $table->decimal('meal_count', 8, 2)->default(1.00);
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: active, 0: cancelled');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_meals');
    }
};
