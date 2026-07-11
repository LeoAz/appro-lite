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
        // On récupère tous les items de facture dont le total est 0
        $items = \App\Models\InvoiceItem::where('total', 0)->get();

        foreach ($items as $item) {
            $item->update(['is_paid' => true]);

            // Si l'item est lié à un chargement, on met à jour le statut du chargement en PAYÉ
            if ($item->load_id) {
                \App\Models\Load::where('id', $item->load_id)->update([
                    'status' => \App\Enums\LoadStatus::Paid
                ]);
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
