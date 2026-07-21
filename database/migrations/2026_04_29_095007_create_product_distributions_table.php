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
        Schema::create('product_distributions', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('floor_id');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('supplier_id');
            $table->string('product_name');
            $table->string('memo_number')->nullable();
            $table->decimal('single_price', 10, 2);
            $table->decimal('total_price_available', 12, 2)->default(0);
            $table->date('purchase_date');
            $table->integer('customer_quantity')->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('product_distributions');
    }
};
