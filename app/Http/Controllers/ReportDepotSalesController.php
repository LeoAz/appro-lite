<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportDepotSalesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return view('app.report-depot-sales');
    }
}
