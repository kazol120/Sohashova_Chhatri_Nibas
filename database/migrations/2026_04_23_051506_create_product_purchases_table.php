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
            Schema::create('product_purchases', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('supplier_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('customer_id')->nullable();

                $table->string('product_name');

                $table->decimal('single_price', 10, 2);
                $table->decimal('total_price', 12, 2);

                $table->integer('quantity');
                $table->integer('available_quantity')->default(0);

                $table->decimal('total_price_available', 12, 2)->default(0);

                $table->date('purchase_date');
                $table->decimal('discount', 10, 2)->default(0)->nullable();

                $table->string('memo_number')->nullable();

                $table->timestamps();
            });
        }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_purchases');
    }
};
