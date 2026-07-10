<?php

namespace App\Livewire\Modals\Client;

use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\Invoice;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use LivewireUI\Modal\ModalComponent;

class ViewClientAccount extends ModalComponent
{
    public Client $client;
    public string $activeTab = 'history';
    public bool $isModal = true;

    public function mount(Client $client = null, bool $isModal = true): void
    {
        if ($client && $client->exists) {
            $this->client = $client;
        } else {
            // Handle cases where client might be null in full page mode
            $this->client = new Client();
        }
        $this->isModal = $isModal;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    #[On("update-client")]
    public function refreshClient()
    {
        $this->client->refresh();
    }

    public function deletePayment($id)
    {
        ClientPayment::destroy($id);
        Notification::make()
            ->title('Règlement supprimé')
            ->success()
            ->send();
        $this->refreshClient();
    }

    public function getInvoicesProperty()
    {
        if (!$this->client->exists) return collect();
        return $this->client->invoices()->orderBy('date', 'desc')->get();
    }

    public function getPaymentsProperty()
    {
        if (!$this->client->exists) return collect();
        return $this->client->payments()->orderBy('date', 'desc')->get();
    }

    public function getHistoryProperty()
    {
        if (!$this->client->exists) return collect();
        $history = collect();

        // Initial Balance
        if ($this->client->initial_balance != 0) {
            $history->push((object)[
                'date' => $this->client->created_at,
                'type' => 'Solde Initial',
                'reference' => '-',
                'debit' => $this->client->initial_balance > 0 ? $this->client->initial_balance : 0,
                'credit' => $this->client->initial_balance < 0 ? abs($this->client->initial_balance) : 0,
                'description' => 'Solde à l\'ouverture du compte',
                'is_payment' => false,
                'id' => null
            ]);
        }

        // Invoices (Debit)
        foreach ($this->client->invoices as $invoice) {
            $history->push((object)[
                'date' => $invoice->date,
                'type' => 'Facture',
                'reference' => $invoice->number,
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'description' => 'Facturation des livraisons',
                'is_payment' => false,
                'id' => $invoice->id
            ]);
        }

        // Payments (Credit)
        foreach ($this->client->payments as $payment) {
            $history->push((object)[
                'date' => $payment->date,
                'type' => 'Avance / Paiement',
                'reference' => $payment->reference ?: '-',
                'debit' => 0,
                'credit' => $payment->amount,
                'description' => ($payment->payment_method ? '['.$payment->payment_method.'] ' : '') . ($payment->note ?: 'Avance client'),
                'is_payment' => true,
                'id' => $payment->id
            ]);
        }

        return $history->sortBy('date');
    }

    public static function modalMaxWidth(): string
    {
        return '6xl';
    }

    #[Layout('layouts.app')]
    public function render()
    {
        if (!isset($this->client) || !$this->client->exists) {
            return <<<'HTML'
                <x-slot name="header">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Compte Client') }}
                    </h2>
                </x-slot>
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 text-center">
                            <p class="text-gray-500">Aucun client sélectionné.</p>
                        </div>
                    </div>
                </div>
            HTML;
        }

        $view = view('livewire.modals.client.view-client-account', [
            'history' => $this->history,
            'invoices' => $this->invoices,
            'payments' => $this->payments,
        ]);

        if (!$this->isModal) {
            return $view->layout('layouts.app', [
                'header' => 'Compte Client : ' . $this->client->nom
            ]);
        }

        return $view;
    }
}
