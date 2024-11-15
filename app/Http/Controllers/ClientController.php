<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __invoke()
    {
        return view("app.client");
    }
}
