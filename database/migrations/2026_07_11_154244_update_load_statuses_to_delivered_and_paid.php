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
        // 1. Convertir "PAYÉ" en "LIVRÉ ET PAYÉ"
        \DB::table('loads')
            ->where('status', 'PAYÉ')
            ->update(['status' => 'LIVRÉ ET PAYÉ']);

        // 2. Convertir "LIVRÉ ET FACTURÉ" en "LIVRÉ ET PAYÉ"
        \DB::table('loads')
            ->where('status', 'LIVRÉ ET FACTURÉ')
            ->update(['status' => 'LIVRÉ ET PAYÉ']);

        // 3. S'assurer que tous les chargements ayant des items payés sont en "LIVRÉ ET PAYÉ"
        $paidLoadIds = \DB::table('invoice_items')
            ->where('is_paid', true)
            ->whereNotNull('load_id')
            ->pluck('load_id')
            ->unique();

        if ($paidLoadIds->isNotEmpty()) {
            \DB::table('loads')
                ->whereIn('id', $paidLoadIds)
                ->update(['status' => 'LIVRÉ ET PAYÉ']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne peut pas facilement différencier ce qui était "PAYÉ" de ce qui était "LIVRÉ ET FACTURÉ"
        // On remet tout en "LIVRÉ ET FACTURÉ" par défaut
        \DB::table('loads')
            ->where('status', 'LIVRÉ ET PAYÉ')
            ->update(['status' => 'LIVRÉ ET FACTURÉ']);
    }
};
