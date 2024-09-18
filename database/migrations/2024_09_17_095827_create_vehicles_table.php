<?php

use App\Enums\VehicleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("vehicles", function (Blueprint $table) {
            $table->id();
            $table->string("chassis")->nullable();
            $table->string("registration");
            $table->string("capacity")->nullable();
            $table->string("driver")->nullable();
            $table->string("contact")->nullable();
            $table
                ->foreignId("carrier_id")
                ->nullable()
                ->constrained("carriers")
                ->onDelete("cascade");
            $table->string("status")->default(VehicleStatus::Available);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("vehicles");
    }
};
