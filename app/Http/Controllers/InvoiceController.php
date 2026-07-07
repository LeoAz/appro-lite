<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return view("app.invoice");
    }

    public function print(Invoice $invoice)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.print', compact('invoice'));
        return $pdf->stream($invoice->number . '.pdf');
    }
}
