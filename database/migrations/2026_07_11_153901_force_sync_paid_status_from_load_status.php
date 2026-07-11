<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\InvoiceItem;
use App\Models\Load;
use App\Enums\LoadStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // On récupère les IDs des chargements qui ont le statut PAYÉ
        $paidLoadIds = Load::where('status', LoadStatus::Paid->value ?? LoadStatus::Paid)
            ->pluck('id');

        if ($paidLoadIds->isNotEmpty()) {
            // On met à jour les invoice_items correspondants pour qu'ils soient marqués comme payés
            // même si le client_payment_id est null
            InvoiceItem::whereIn('load_id', $paidLoadIds)
                ->update(['is_paid' => true]);
        }

        // Réciproquement, si un invoice_item est marqué is_paid, le Load doit être Paid
        $isPaidLoadIds = InvoiceItem::where('is_paid', true)
            ->whereNotNull('load_id')
            ->pluck('load_id')
            ->unique();

        if ($isPaidLoadIds->isNotEmpty()) {
            Load::whereIn('id', $isPaidLoadIds)
                ->update(['status' => LoadStatus::Paid]);
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
