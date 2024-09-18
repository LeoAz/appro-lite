<?php

use App\Http\Controllers\CarrierController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
    Route::get("/vehicles", VehicleController::class)->name("vehicles");
    Route::get("/", CarrierController::class)->name("carriers");
    Route::get("/depots", DepotController::class)->name("depots");
    Route::get("/cities", CityController::class)->name("cities");
    Route::get("/loads", LoadController::class)->name("loads");

    Route::get("/profile", [ProfileController::class, "edit"])->name(
        "profile.edit"
    );
    Route::patch("/profile", [ProfileController::class, "update"])->name(
        "profile.update"
    );
    Route::delete("/profile", [ProfileController::class, "destroy"])->name(
        "profile.destroy"
    );
});

require __DIR__ . "/auth.php";
