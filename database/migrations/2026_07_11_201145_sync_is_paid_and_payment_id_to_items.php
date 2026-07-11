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
        // Synchroniser invoice_items
        DB::table('invoice_items')
            ->whereNotNull('client_payment_id')
            ->update(['is_paid' => true]);

        // Synchroniser depot_invoice_items
        DB::table('depot_invoice_items')
            ->whereNotNull('client_payment_id')
            ->update(['is_paid' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
