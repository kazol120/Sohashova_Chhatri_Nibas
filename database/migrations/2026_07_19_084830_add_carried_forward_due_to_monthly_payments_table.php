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
        Schema::table('monthly_payments', function (Blueprint $table) {
            // আগের মাসের বকেয়া যা এই মাসে carry করা হয়েছে
            $table->decimal('carried_forward_due', 10, 2)->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_payments', function (Blueprint $table) {
            $table->dropColumn('carried_forward_due');
        });
    }
};
