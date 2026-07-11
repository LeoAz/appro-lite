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
        // On s'assure que tous les items qui ont un client_payment_id sont marqués comme payés
        DB::table('invoice_items')
            ->whereNotNull('client_payment_id')
            ->update(['is_paid' => true]);

        // Optionnel : s'assurer que ceux qui n'ont pas de paiement sont marqués comme non payés
        // (Sauf s'il y a d'autres moyens de marquer comme payé, mais dans ce système c'est lié au client_payment_id)
        DB::table('invoice_items')
            ->whereNull('client_payment_id')
            ->update(['is_paid' => false]);

        // Mettre à jour le statut des chargements (Load) basés sur les items payés
        // Si tous les items d'un chargement sont payés, le chargement est payé.
        // Mais ici on va faire simple: si un load est lié à un invoice_item payé, on vérifie son état.
        $paidLoadIds = DB::table('invoice_items')
            ->where('is_paid', true)
            ->whereNotNull('load_id')
            ->pluck('load_id')
            ->unique();

        foreach ($paidLoadIds as $loadId) {
            DB::table('loads')
                ->where('id', $loadId)
                ->update(['status' => 'PAYÉ']);
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
