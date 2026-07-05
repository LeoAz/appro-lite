<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FuelPurchaseController extends Controller
{
    public function __invoke()
    {
        return view('app.fuel-purchase');
    }
}
