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
        Schema::table('loads', function (Blueprint $table) {
            // Ajouter client_payment_id s'il n'existe pas déjà (sécurité)
            if (!Schema::hasColumn('loads', 'client_payment_id')) {
                $table->foreignId('client_payment_id')->nullable()->after('status')->constrained('client_payments')->nullOnDelete();
            }

            $table->boolean('is_paid')->default(false)->after('status');
        });

        // Synchroniser is_paid basé sur client_payment_id existant
        DB::table('loads')
            ->whereNotNull('client_payment_id')
            ->update([
                'is_paid' => true,
                'updated_at' => now()
            ]);
    }

    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->dropForeign(['client_payment_id']);
            $table->dropColumn(['is_paid', 'client_payment_id']);
        });
    }
};
