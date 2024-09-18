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
        Schema::table("vehicles", function (Blueprint $table) {
            $table->dropColumn("driver");
            $table->dropColumn("contact");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("vehicles", function (Blueprint $table) {
            $table->string("driver");
            $table->string("contact");
        });
    }
};
