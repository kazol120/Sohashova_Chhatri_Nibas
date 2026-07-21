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
        Schema::create('residence_overviews', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('img_back');
            $table->string('img_front');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residence_overviews');
    }
};
