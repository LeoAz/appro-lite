<?php

use App\Http\Controllers\CarrierController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
    Route::get("/vehicles", VehicleController::class)->name("vehicles");
    Route::get("/", CarrierController::class)->name("carriers");
    Route::get("/clients", ClientController::class)->name("clients");
    Route::get("/depots", DepotController::class)->name("depots");
    Route::get("/depots/{depot}", [DepotController::class, 'show'])->name("depots.show");
    Route::get("/cities", CityController::class)->name("cities");
    Route::get("/loads", LoadController::class)->name("loads");
    Route::get("/deliveries", \App\Http\Controllers\DeliveryController::class)->name("deliveries");
    Route::get("/fuel-purchases", \App\Http\Controllers\FuelPurchaseController::class)->name("fuel-purchases");
    Route::get("/reports/loads", \App\Http\Controllers\ReportLoadController::class)->name("reports.loads");
    Route::get("/reports/deliveries", \App\Http\Controllers\ReportDeliveryController::class)->name("reports.deliveries");
    Route::get("/reports/stocks", \App\Http\Controllers\ReportStockController::class)->name("reports.stocks");

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
