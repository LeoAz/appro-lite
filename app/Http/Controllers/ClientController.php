<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __invoke()
    {
        return view("app.client");
    }

    public function printAccount(Client $client)
    {
        $history = collect();

        // Initial Balance
        if ($client->initial_balance != 0) {
            $history->push((object)[
                'date' => $client->created_at,
                'type' => 'Solde Initial',
                'reference' => '-',
                'debit' => $client->initial_balance > 0 ? $client->initial_balance : 0,
                'credit' => $client->initial_balance < 0 ? abs($client->initial_balance) : 0,
                'description' => 'Solde à l\'ouverture du compte',
            ]);
        }

        // Invoices (Debit)
        foreach ($client->invoices as $invoice) {
            $history->push((object)[
                'date' => $invoice->date,
                'type' => 'Facture',
                'reference' => $invoice->number,
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'description' => 'Facturation des livraisons',
            ]);
        }

        // Payments (Credit)
        foreach ($client->payments as $payment) {
            $history->push((object)[
                'date' => $payment->date,
                'type' => 'Avance / Paiement',
                'reference' => $payment->reference ?: '-',
                'debit' => 0,
                'credit' => $payment->amount,
                'description' => ($payment->payment_method ? '['.$payment->payment_method.'] ' : '') . ($payment->note ?: 'Avance client'),
            ]);
        }

        $history = $history->sortBy('date');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('clients.account-pdf', compact('client', 'history'));
        return $pdf->stream('Relevé_' . str_replace(' ', '_', $client->nom) . '_' . now()->format('dmY') . '.pdf');
    }
}
