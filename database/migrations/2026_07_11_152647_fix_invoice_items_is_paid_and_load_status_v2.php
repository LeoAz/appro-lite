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
        // 1. Marquer les invoice_items comme payés s'ils ont un client_payment_id
        \DB::table('invoice_items')
            ->whereNotNull('client_payment_id')
            ->update(['is_paid' => true]);

        // 2. Mettre à jour le statut des chargements associés en 'PAYÉ'
        $paidLoadIds = \DB::table('invoice_items')
            ->whereNotNull('client_payment_id')
            ->pluck('load_id')
            ->filter()
            ->unique();

        if ($paidLoadIds->isNotEmpty()) {
            \DB::table('loads')
                ->whereIn('id', $paidLoadIds)
                ->update(['status' => 'PAYÉ']);
        }

        // 3. Cas inverse : si pas de client_payment_id, s'assurer que is_paid est false
        \DB::table('invoice_items')
            ->whereNull('client_payment_id')
            ->update(['is_paid' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
