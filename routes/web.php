<?php

use App\Http\Controllers\CarrierController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get("/vehicles", VehicleController::class)->name("vehicles");
Route::get("/carriers", CarrierController::class)->name("carriers");
Route::get("/depots", DepotController::class)->name("depots");
Route::get("/loads", LoadController::class)->name("loads");

Route::get("/dashboard", function () {
    return view("dashboard");
})
    ->middleware(["auth", "verified"])
    ->name("dashboard");

Route::middleware("auth")->group(function () {
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
