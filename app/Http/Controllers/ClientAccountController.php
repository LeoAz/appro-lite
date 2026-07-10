<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientAccountController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return view('app.client-account');
    }
}
