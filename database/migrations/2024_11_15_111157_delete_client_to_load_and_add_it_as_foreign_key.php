<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("loads", function (Blueprint $table) {
            $table->dropColumn("client");
            $table
                ->foreignId("client_id")
                ->nullable()
                ->constrained("clients")
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("loads", function (Blueprint $table) {
            $table->string("client");
            $table->dropConstrainedForeignId("client_id");
        });
    }
};
