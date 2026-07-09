<?php

namespace App\Livewire\Report;

use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class ClientStatementReport extends Component implements HasForms
{
    use InteractsWithForms;

    public $client_id;
    public $date_from;
    public $date_to;

    public function mount()
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->label('Client')
                    ->options(Client::pluck('nom', 'id'))
                    ->searchable()
                    ->live()
                    ->required()
                    ->placeholder('Sélectionner un client'),
                DatePicker::make('date_from')
                    ->label('Période du')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live(),
                DatePicker::make('date_to')
                    ->label('au')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live(),
            ])
            ->columns(3);
    }

    public function getStatementDataProperty(): Collection
    {
        if (!$this->client_id) {
            return collect();
        }

        $client = Client::find($this->client_id);
        if (!$client) {
            return collect();
        }

        $transactions = collect();

        // 1. Initial Balance / Balance before period
        $initialBalance = $client->initial_balance;

        $previousInvoices = Invoice::where('client_id', $this->client_id)
            ->where('date', '<', $this->date_from)
            ->sum('total_amount');

        $previousPayments = ClientPayment::where('client_id', $this->client_id)
            ->where('date', '<', $this->date_from)
            ->sum('amount');

        $openingBalance = $initialBalance + $previousInvoices - $previousPayments;

        $transactions->push([
            'date' => $this->date_from,
            'operation' => 'REPORT DE SOLDE',
            'type' => 'report',
            'debit' => 0,
            'credit' => $openingBalance,
            'sort_date' => '0000-00-00',
        ]);

        // 2. Invoices
        $invoices = Invoice::where('client_id', $this->client_id)
            ->whereBetween('date', [$this->date_from, $this->date_to])
            ->with('items.delivery')
            ->get();

        foreach ($invoices as $invoice) {
            $loadNames = $invoice->items->map(function($item) {
                return $item->delivery?->vehicle_registration;
            })->filter()->unique()->implode(', ');

            $totalLitres = $invoice->items->sum('quantity_delivered');

            $transactions->push([
                'id' => $invoice->id,
                'date' => $invoice->date->format('Y-m-d'),
                'operation' => "Facture #{$invoice->number}",
                'type' => 'invoice',
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'sort_date' => $invoice->date->format('Y-m-d') . '_1',
            ]);
        }

        // 3. Payments
        $payments = ClientPayment::where('client_id', $this->client_id)
            ->whereBetween('date', [$this->date_from, $this->date_to])
            ->get();

        foreach ($payments as $payment) {
            $operation = "Paiement #{$payment->reference}";
            if ($payment->payment_method) {
                $operation .= " ({$payment->payment_method})";
            }
            if ($payment->note) {
                $operation .= " - {$payment->note}";
            }

            $transactions->push([
                'date' => $payment->date->format('Y-m-d'),
                'operation' => $operation,
                'type' => 'payment',
                'debit' => 0,
                'credit' => $payment->amount,
                'sort_date' => $payment->date->format('Y-m-d') . '_2',
            ]);
        }

        return $transactions->sortBy('sort_date')->values();
    }

    public function downloadPdf()
    {
        if (!$this->client_id) return;

        $client = Client::find($this->client_id);
        $transactions = $this->statementData;

        $totalDebit = $transactions->where('type', '!=', 'report')->sum('debit');
        $totalCredit = $transactions->where('type', '!=', 'report')->sum('credit');

        $reportLine = $transactions->firstWhere('type', 'report');
        $openingBalance = $reportLine['credit'] ?? 0;

        $finalBalance = $openingBalance + $totalDebit - $totalCredit;

        $pdf = Pdf::loadView('livewire.report.print-client-statement', [
            'client' => $client,
            'transactions' => $transactions,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'openingBalance' => $openingBalance,
            'finalBalance' => $finalBalance,
            'date' => now(),
        ]);

        return response()->streamDownload(
            fn () => print $pdf->output(),
            "Situation_Client_" . str_replace(' ', '_', $client->nom) . "_" . now()->format('d_m_Y') . ".pdf"
        );
    }

    public function render()
    {
        return view('livewire.report.client-statement-report', [
            'transactions' => $this->statementData,
        ])->layout('layouts.app');
    }
}
