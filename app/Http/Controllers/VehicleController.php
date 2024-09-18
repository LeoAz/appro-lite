<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __invoke()
    {
        return view("app.vehicle");
    }
}
