<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoadController extends Controller
{
    public function __invoke()
    {
        return view("app.load");
    }
}
