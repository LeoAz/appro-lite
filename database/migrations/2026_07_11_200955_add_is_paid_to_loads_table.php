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
            $table->boolean('is_paid')->default(false)->after('status');
        });

        // Synchroniser is_paid basé sur client_payment_id existant
        \App\Models\Load::whereNotNull('client_payment_id')->update(['is_paid' => true]);
    }

    public function down(): void
    {
        Schema::table('loads', function (Blueprint $table) {
            $table->dropColumn('is_paid');
        });
    }
};
