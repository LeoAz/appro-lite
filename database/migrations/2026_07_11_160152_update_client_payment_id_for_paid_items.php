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
        // On essaie de retrouver le client_payment_id pour les items marqués comme payés mais sans ID de paiement
        // On se base sur le fait que le chargement (Load) pourrait être lié à un paiement via d'autres items
        // Ou via une recherche dans la table client_payment_items si elle existe, mais ici on va chercher la cohérence.

        $itemsToFix = \DB::table('invoice_items')
            ->where('is_paid', true)
            ->whereNull('client_payment_id')
            ->get();

        foreach ($itemsToFix as $item) {
            // Chercher un autre item du même chargement qui a un client_payment_id
            $existingPayment = \DB::table('invoice_items')
                ->where('load_id', $item->load_id)
                ->whereNotNull('client_payment_id')
                ->first();

            if ($existingPayment) {
                \DB::table('invoice_items')
                    ->where('id', $item->id)
                    ->update(['client_payment_id' => $existingPayment->client_payment_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
