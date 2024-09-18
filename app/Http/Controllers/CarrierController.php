<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function __invoke()
    {
        return view("app.carrier");
    }
}
