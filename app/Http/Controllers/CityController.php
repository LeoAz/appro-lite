<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __invoke()
    {
        return view("app.city");
    }
}
