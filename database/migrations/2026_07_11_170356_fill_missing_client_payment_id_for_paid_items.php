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
        // 1. Essayer de lier les items payés sans ID de paiement à un paiement existant
        // On cherche des paiements de type 'load' qui ont été créés à la même date que la facture ou après,
        // et qui correspondent au client.

        $itemsToFix = \DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoice_items.is_paid', true)
            ->whereNull('invoice_items.client_payment_id')
            ->select('invoice_items.*', 'invoices.client_id', 'invoices.date as invoice_date')
            ->get();

        foreach ($itemsToFix as $item) {
            // On cherche un paiement pour ce client qui pourrait correspondre
            // Idéalement on cherche un paiement qui a été fait après la facture
            $potentialPayment = \DB::table('client_payments')
                ->where('client_id', $item->client_id)
                ->where('payment_type', 'load')
                ->where('date', '>=', $item->invoice_date)
                // On essaie de trouver un paiement dont le montant contient ce total
                ->where('amount', '>=', $item->total)
                ->orderBy('date', 'asc')
                ->first();

            if ($potentialPayment) {
                \DB::table('invoice_items')
                    ->where('id', $item->id)
                    ->update(['client_payment_id' => $potentialPayment->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne peut pas facilement annuler cette migration de données sans risquer de corrompre des liens valides
    }
};
