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
        Schema::create('special_meals', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('title')->nullable();
            $table->decimal('full_rate', 10, 2);
            $table->decimal('half_rate', 10, 2)->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_meals');
    }
};
