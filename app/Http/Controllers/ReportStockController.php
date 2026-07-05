<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportStockController extends Controller
{
    public function __invoke()
    {
        return view('app.report-stock');
    }
}
