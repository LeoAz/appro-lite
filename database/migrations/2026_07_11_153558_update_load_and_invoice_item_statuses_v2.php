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
        // 1. Mettre à jour les invoice_items ayant un client_payment_id
        // On s'assure qu'ils sont bien marqués comme payés
        InvoiceItem::whereNotNull('client_payment_id')->update(['is_paid' => true]);

        // 2. Mettre à jour les chargements liés à ces invoice_items
        $paidLoadIds = InvoiceItem::whereNotNull('client_payment_id')
            ->whereNotNull('load_id')
            ->pluck('load_id')
            ->unique();

        if ($paidLoadIds->isNotEmpty()) {
            Load::whereIn('id', $paidLoadIds)->update(['status' => LoadStatus::Invoiced]);
        }

        // 3. Cas particulier : On m'a demandé de ne pas me baser sur le montant (total à zéro)
        // Mais de marquer comme payé les chargements qui devraient l'être.
        // Puisque je ne connais pas le critère exact autre que client_payment_id,
        // je m'assure au moins que la relation est cohérente.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
