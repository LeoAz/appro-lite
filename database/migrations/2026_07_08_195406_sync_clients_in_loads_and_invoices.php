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
        $clientNames = DB::table('loads')
            ->whereNotNull('client_name')
            ->where('client_name', '!=', '')
            ->pluck('client_name')
            ->merge(
                DB::table('invoices')
                    ->whereNotNull('client_name')
                    ->where('client_name', '!=', '')
                    ->pluck('client_name')
            )
            ->unique();

        foreach ($clientNames as $name) {
            $clientId = DB::table('clients')->updateOrInsert(
                ['nom' => $name],
                ['updated_at' => now(), 'created_at' => now()]
            );

            $client = DB::table('clients')->where('nom', $name)->first();

            if ($client) {
                DB::table('loads')
                    ->where('client_name', $name)
                    ->update(['client_id' => $client->id]);

                DB::table('invoices')
                    ->where('client_name', $name)
                    ->update(['client_id' => $client->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas d'action spécifique pour le retour en arrière car nous ne voulons pas supprimer les clients créés
    }
};
