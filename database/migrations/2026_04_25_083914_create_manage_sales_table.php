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
        Schema::create('manage_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('supplier_id');
            $table->string('product_name');
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
        Schema::dropIfExists('manage_sales');
    }
};
