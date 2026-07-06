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
        Schema::table('fuel_purchases', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->nullable()->change();
            $table->decimal('total_price', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuel_purchases', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->nullable(false)->change();
            $table->decimal('total_price', 15, 2)->nullable(false)->change();
        });
    }
};
