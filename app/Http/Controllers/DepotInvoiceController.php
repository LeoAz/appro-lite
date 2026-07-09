<?php

namespace App\Http\Controllers;

use App\Models\DepotInvoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class DepotInvoiceController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return view("app.depot-invoice");
    }

    public function print(DepotInvoice $invoice)
    {
        $pdf = Pdf::loadView('depot-invoices.print', compact('invoice'));
        return $pdf->stream($invoice->number . '.pdf');
    }
}
