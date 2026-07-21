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
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('nid_passport')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('division_id')->nullable();;
            $table->integer('district_id')->nullable();;
            $table->integer('thana_id')->nullable();;
            $table->text('permanent_address')->nullable();
            $table->string('designation')->nullable();;
            $table->string('department')->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->date('joining_date')->nullable();
            $table->string('shift_time')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('staffs');
    Schema::enableForeignKeyConstraints();
}
};
