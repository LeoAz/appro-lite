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
        // 1. Effacer l'ensemble des données de la table client
        // On désactive les contraintes de clés étrangères pour pouvoir vider la table si nécessaire
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\Client::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Lister l'ensemble des clients saisis dans la table loads (sans doublons)
        $clientNames = \Illuminate\Support\Facades\DB::table('loads')
            ->whereNotNull('client_name')
            ->where('client_name', '!=', '')
            ->distinct()
            ->pluck('client_name');

        // 3. Créer la liste des clients
        foreach ($clientNames as $name) {
            \App\Models\Client::create([
                'nom' => $name,
                'initial_balance' => 0
                // Les autres champs sont facultatifs d'après les précédentes instructions
            ]);
        }

        // 4. Mettre à jour le client_id dans la table loads
        $clients = \App\Models\Client::all();
        foreach ($clients as $client) {
            \Illuminate\Support\Facades\DB::table('loads')
                ->where('client_name', $client->nom)
                ->update(['client_id' => $client->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
};
