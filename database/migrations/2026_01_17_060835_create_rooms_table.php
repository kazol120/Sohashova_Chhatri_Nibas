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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('floor_id');
            $table->string('room_no')->nullable();
            $table->json('image')->nullable();
            $table->integer('acstatus')->nullable();
            $table->string('room_size')->nullable();
            $table->string('breakfast')->nullable();
            $table->string('attached_bathroom')->nullable();
            $table->string('max_people')->nullable();
            $table->string('balcony')->nullable();
            $table->string('ac_status')->nullable();
            $table->string('price')->nullable();
            $table->string('windows')->nullable();
            $table->string('room_type')->nullable();
            $table->string('max_discount')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
