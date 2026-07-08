<?php

namespace App\Livewire\Modals\Client;

use App\Models\Client;
use LivewireUI\Modal\ModalComponent;

class ViewClientAccount extends ModalComponent
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function getHistoryProperty()
    {
        $history = collect();

        // Initial Balance
        if ($this->client->initial_balance != 0) {
            $history->push([
                'date' => $this->client->created_at,
                'type' => 'Solde Initial',
                'reference' => '-',
                'debit' => $this->client->initial_balance > 0 ? $this->client->initial_balance : 0,
                'credit' => $this->client->initial_balance < 0 ? abs($this->client->initial_balance) : 0,
                'description' => 'Solde à l\'ouverture du compte',
            ]);
        }

        // Invoices (Debit)
        foreach ($this->client->invoices as $invoice) {
            $history->push([
                'date' => $invoice->date,
                'type' => 'Facture',
                'reference' => $invoice->number,
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'description' => 'Facturation des livraisons',
            ]);
        }

        // Payments (Credit)
        foreach ($this->client->payments as $payment) {
            $history->push([
                'date' => $payment->date,
                'type' => 'Avance / Paiement',
                'reference' => $payment->reference ?: '-',
                'debit' => 0,
                'credit' => $payment->amount,
                'description' => $payment->note ?: 'Avance client',
            ]);
        }

        return $history->sortBy('date');
    }

    public static function modalMaxWidth(): string
    {
        return '6xl';
    }

    public function render()
    {
        return view('livewire.modals.client.view-client-account', [
            'history' => $this->history,
        ]);
    }
}
