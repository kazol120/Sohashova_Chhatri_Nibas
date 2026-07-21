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
        Schema::create('staff_salary_payments', function (Blueprint $table) {

            $table->id();

            // staff id
            $table->unsignedBigInteger('staff_id');

            // month & year
            $table->unsignedTinyInteger('salary_month'); // 1-12
            $table->unsignedSmallInteger('salary_year'); // 2026

            // advance or full
            $table->enum('payment_type', ['advance', 'full']);

            // amount
            $table->decimal('amount', 12, 2)->default(0);

            // payment date
            $table->date('payment_date');

            // note
            $table->text('note')->nullable();

            // created by
            $table->unsignedBigInteger('created_by')->nullable();

            // status
            $table->tinyInteger('status')->default(1);

            $table->timestamps();

            // foreign key (IMPORTANT)
            $table->foreign('staff_id')
                  ->references('id')
                  ->on('staffs') // ⚠️ change to 'staff' if needed
                  ->onDelete('cascade');

            // index for fast query
            $table->index(['staff_id', 'salary_month', 'salary_year'], 'salary_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_salary_payments');
    }
};