<?php

use App\Enums\LoadStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("loads", function (Blueprint $table) {
            $table->id();
            $table->dateTime("load_date");
            $table->string("load_location");
            $table->string("product");
            $table->string("capacity");
            $table
                ->foreignId("vehicle_id")
                ->nullable()
                ->constrained("vehicles")
                ->onDelete("cascade");
            $table
                ->foreignId("depot_id")
                ->nullable()
                ->constrained("depots")
                ->onDelete("cascade");
            $table->boolean("is_unload")->default(false);
            $table->dateTime("unload_date")->nullable();
            $table->string("unload_location")->nullable();
            $table->string("status")->default(LoadStatus::Pending);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("loads");
    }
};
