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
        // 1. S'assurer que la colonne client_id existe (elle devrait déjà exister d'après model:show)
        if (!Schema::hasColumn('invoices', 'client_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('client_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        // 2. Reprise de données pour les invoices qui n'ont pas de client_id mais qui ont un client_name
        $invoices = DB::table('invoices')
            ->whereNull('client_id')
            ->whereNotNull('client_name')
            ->get();

        foreach ($invoices as $invoice) {
            $client = DB::table('clients')->where('nom', $invoice->client_name)->first();
            if ($client) {
                DB::table('invoices')
                    ->where('id', $invoice->id)
                    ->update(['client_id' => $client->id]);
            }
        }

        // 3. Si toujours pas de client_id, essayer de le récupérer via les loads associés
        $invoicesStillNull = DB::table('invoices')
            ->whereNull('client_id')
            ->get();

        foreach ($invoicesStillNull as $invoice) {
            $loadId = DB::table('invoice_items')
                ->where('invoice_id', $invoice->id)
                ->value('load_id');

            if ($loadId) {
                $clientIdFromLoad = DB::table('loads')
                    ->where('id', $loadId)
                    ->value('client_id');

                if ($clientIdFromLoad) {
                    DB::table('invoices')
                        ->where('id', $invoice->id)
                        ->update(['client_id' => $clientIdFromLoad]);

                    // Mettre à jour aussi le client_name si possible pour la cohérence
                    $clientNom = DB::table('clients')->where('id', $clientIdFromLoad)->value('nom');
                    if ($clientNom) {
                        DB::table('invoices')
                            ->where('id', $invoice->id)
                            ->update(['client_name' => $clientNom]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne fait rien de particulier car on veut garder les données
    }
};
