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
        Schema::table('depot_invoice_items', function (Blueprint $table) {
            $table->foreignId('client_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_paid')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('depot_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['client_payment_id']);
            $table->dropColumn(['client_payment_id', 'is_paid']);
        });
    }
};
