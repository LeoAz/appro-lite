<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DepotController extends Controller
{
    public function __invoke()
    {
        return view("app.depot");
    }
}
