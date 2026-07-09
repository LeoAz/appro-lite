<?php

use App\Http\Controllers\CarrierController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware("auth")->group(function () {
    Route::get("/", CarrierController::class)->name("carriers");
    Route::get("/clients", ClientController::class)->name("clients");
    Route::get("/clients/{client}/account/pdf", [ClientController::class, 'printAccount'])->name("client.account.pdf");
    Route::get("/depots", DepotController::class)->name("depots");
    Route::get("/depots/{depot}", [DepotController::class, 'show'])->name("depots.show");
    Route::get("/loads", LoadController::class)->name("loads");
    Route::get("/deliveries", \App\Http\Controllers\DeliveryController::class)->name("deliveries");
    Route::get("/fuel-purchases", \App\Http\Controllers\FuelPurchaseController::class)->name("fuel-purchases");
    Route::get("/reports/loads", \App\Http\Controllers\ReportLoadController::class)->name("reports.loads");
    Route::get("/reports/deliveries", \App\Http\Controllers\ReportDeliveryController::class)->name("reports.deliveries");
    Route::get("/reports/stocks", \App\Http\Controllers\ReportStockController::class)->name("reports.stocks");
    Route::get("/reports/sales", \App\Http\Controllers\ReportSalesController::class)->name("reports.sales");
    Route::get("/reports/depot-sales", \App\Livewire\Report\DepotSalesReport::class)->name("reports.depot-sales");
    Route::get("/reports/client-statement", \App\Livewire\Report\ClientStatementReport::class)->name("reports.client-statement");

    Route::get("/invoices", \App\Http\Controllers\InvoiceController::class)->name("invoices");
    Route::get("/invoices/{invoice}/print", [\App\Http\Controllers\InvoiceController::class, 'print'])->name("invoices.print");

    Route::get("/depot-invoices", \App\Http\Controllers\DepotInvoiceController::class)->name("depot-invoices");
    Route::get("/depot-invoices/{invoice}/print", [\App\Http\Controllers\DepotInvoiceController::class, 'print'])->name("depot-invoices.print");

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
