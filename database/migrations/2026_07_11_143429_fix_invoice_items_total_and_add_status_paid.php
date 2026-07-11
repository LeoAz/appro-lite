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
        // Restaurer les totaux à zero pour les items qui ont un total de 0 mais une quantité/prix non nuls
        // Ou plus généralement, s'assurer que le total = (quantité - manquant) * prix
        $items = \App\Models\InvoiceItem::where('total', 0)->get();
        foreach ($items as $item) {
            $item->total = ($item->quantity_delivered - $item->missing_quantity) * $item->unit_price;
            $item->save();

            // Mettre à jour la facture parente si nécessaire
            if ($item->invoice) {
                $item->invoice->update([
                    'total_amount' => $item->invoice->items()->sum('total')
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
