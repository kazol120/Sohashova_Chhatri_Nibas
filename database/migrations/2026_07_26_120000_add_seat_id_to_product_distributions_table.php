<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_distributions', 'seat_id')) {
            Schema::table('product_distributions', function (Blueprint $table) {
                $table->unsignedBigInteger('seat_id')->nullable()->after('room_id');
                $table->foreign('seat_id')->references('id')->on('room_seats')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::table('product_distributions', function (Blueprint $table) {
            $table->dropForeign(['seat_id']);
            $table->dropColumn('seat_id');
        });
    }
};
