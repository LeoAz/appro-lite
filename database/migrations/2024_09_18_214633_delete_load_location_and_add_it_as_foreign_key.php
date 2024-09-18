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
            $table->dropColumn("load_location");
            $table
                ->foreignId("city_id")
                ->nullable()
                ->constrained("cities")
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("loads", function (Blueprint $table) {
            $table->string("load_location");
            $table->dropConstrainedForeignId("city_id");
        });
    }
};
