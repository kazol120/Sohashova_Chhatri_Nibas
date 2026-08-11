<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->enum('request_type', ['off', 'full', 'half_day', 'half_night']);
            $table->tinyInteger('status')->default(0); // 0 = Pending, 1 = Approved, 2 = Rejected
            $table->boolean('user_notified')->default(false);
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_requests');
    }
};
