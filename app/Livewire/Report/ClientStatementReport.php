<?php

namespace App\Livewire\Report;

use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\Invoice;
use App\Models\DepotInvoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Filament\Tables\Columns\Summarizers\Sum;

use App\Models\InvoiceItem;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ClientStatementReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $client_id;
    public $date_from;
    public $date_to;
    public $showActions = true;
    public $activeTab = 'statement';

    public function mount($showActions = true)
    {
        $this->showActions = $showActions;
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetTable();
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
                    ->placeholder('Sélectionner un client')
                    ->afterStateUpdated(fn () => $this->resetTable()),
                DatePicker::make('date_from')
                    ->label('Période du')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->hidden(fn () => $this->activeTab === 'receivables' || $this->activeTab === 'payment_history'),
                DatePicker::make('date_to')
                    ->label('au')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->hidden(fn () => $this->activeTab === 'receivables' || $this->activeTab === 'payment_history'),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InvoiceItem::query()
                    ->whereHas('invoice', function ($query) {
                        if ($this->client_id) {
                            $query->where('client_id', $this->client_id);
                        }
                    })
                    ->when($this->activeTab === 'receivables', fn($query) => $query->where('is_paid', false))
                    ->when($this->activeTab === 'payment_history', fn($query) => $query->where('is_paid', true))
                    ->with(['invoice.client', 'delivery', 'payment'])
            )
            ->columns([
                TextColumn::make('invoice.number')
                    ->label('N° Facture')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('invoice.date')
                    ->label('Date Facture')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('invoice.client.nom')
                    ->label('Client')
                    ->sortable()
                    ->searchable()
                    ->hidden(fn () => !empty($this->client_id)),
                TextColumn::make('delivery.vehicle_registration')
                    ->label('Véhicule')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('delivery.product')
                    ->label('Produit'),
                TextColumn::make('quantity_delivered')
                    ->label('Qté Facturée')
                    ->numeric()
                    ->suffix(' L'),
                TextColumn::make('total')
                    ->label(fn() => $this->activeTab === 'receivables' ? 'Montant Dû' : 'Montant Payé')
                    ->numeric()
                    ->suffix(' FCFA')
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('payment.date')
                    ->label('Date Paiement')
                    ->date('d/m/Y')
                    ->sortable()
                    ->visible(fn() => $this->activeTab === 'payment_history'),
                TextColumn::make('payment.reference')
                    ->label('Réf. Règlement')
                    ->searchable()
                    ->visible(fn() => $this->activeTab === 'payment_history'),
                IconColumn::make('is_paid')
                    ->label('Statut')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->visible(fn() => $this->activeTab === 'statement'), // On ne le montre que dans le relevé si besoin, mais ici la table n'est pas utilisée pour le relevé
            ])
            ->defaultSort('invoice.date', 'asc')
            ->filters([
                // Filtres supprimés car on utilise les onglets maintenant
            ])
            ->emptyStateHeading($this->activeTab === 'receivables' ? 'Aucune créance pour ce client' : 'Aucun historique de paiement');
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

        $previousDepotInvoices = DepotInvoice::where('client_id', $this->client_id)
            ->where('date', '<', $this->date_from)
            ->sum('total_amount');

        $previousPayments = ClientPayment::where('client_id', $this->client_id)
            ->where('date', '<', $this->date_from)
            ->whereNull('parent_id') // Exclure les règlements via avance pour éviter double compte
            ->sum('amount');

        $openingBalance = $initialBalance + $previousInvoices + $previousDepotInvoices - $previousPayments;

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

        // 2.b Depot Invoices
        $depotInvoices = DepotInvoice::where('client_id', $this->client_id)
            ->whereBetween('date', [$this->date_from, $this->date_to])
            ->get();

        foreach ($depotInvoices as $invoice) {
            $transactions->push([
                'id' => $invoice->id,
                'date' => $invoice->date->format('Y-m-d'),
                'operation' => "Facture Dépôt #{$invoice->number}",
                'type' => 'depot_invoice',
                'debit' => $invoice->total_amount,
                'credit' => 0,
                'sort_date' => $invoice->date->format('Y-m-d') . '_1',
            ]);
        }

        // 3. Payments and Advances
        $payments = ClientPayment::where('client_id', $this->client_id)
            ->whereBetween('date', [$this->date_from, $this->date_to])
            ->get();

        foreach ($payments as $payment) {
            // Ne pas afficher les règlements qui utilisent une avance (pour éviter double compte crédit)
            // OU BIEN afficher l'utilisation de l'avance mais avec un montant débit/crédit neutre dans le calcul global?
            // En fait, le montant de l'avance est déjà compté en crédit.
            // Si on utilise l'avance pour payer une facture, le solde change ?
            // Facture 1000 (Débit 1000)
            // Avance 1000 (Crédit 1000) -> Solde 0
            // Règlement via avance 1000 -> Si on remet Crédit 1000, Solde -1000 (Faux)
            // Donc le règlement par avance ne doit pas rajouter de crédit, mais il "consomme" l'avance.

            if ($payment->is_advance) {
                $operation = "AVANCE CLIENT #{$payment->reference}";
                $type = 'advance';
            } elseif ($payment->parent_id) {
                $typeName = $payment->payment_type === 'depot' ? 'Règlement Dépôt via Avance' : 'Règlement Chargement via Avance';
                $operation = "{$typeName} #{$payment->reference} (Utilisation Avance)";
                $type = 'payment_via_advance';
            } else {
                $typeName = $payment->payment_type === 'depot' ? 'Règlement Dépôt' : 'Règlement Chargement';
                $operation = "{$typeName} #{$payment->reference}";
                $type = 'payment';
            }

            // Si c'est un règlement sur chargement, mentionner les véhicules concernés
            if (!$payment->is_advance && $payment->payment_type === 'load') {
                $vehicles = $payment->invoiceItems()
                    ->with('delivery')
                    ->get()
                    ->map(fn($item) => $item->delivery?->vehicle_registration)
                    ->filter()
                    ->unique()
                    ->implode(', ');

                if ($vehicles) {
                    $operation .= " [Vhc: {$vehicles}]";
                }
            }

            if ($payment->payment_method) {
                $operation .= " ({$payment->payment_method})";
            }

            // Détailler les items payés par ce règlement
            $itemsPaid = $payment->invoiceItems()->with('delivery')->get();
            if ($itemsPaid->count() > 0) {
                $details = $itemsPaid->map(function($item) {
                    return $item->delivery ? "{$item->delivery->vehicle_registration} ({$item->quantity_delivered}L)" : "Item #{$item->id}";
                })->implode(', ');
                $operation .= " - Payé: " . $details;
            }

            if ($payment->note) {
                $operation .= " - {$payment->note}";
            }

            $transactions->push([
                'id' => $payment->id,
                'date' => $payment->date->format('Y-m-d'),
                'operation' => $operation,
                'type' => $type,
                'debit' => 0,
                'credit' => $payment->parent_id ? 0 : $payment->amount,
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

        $receivables = InvoiceItem::query()
            ->whereHas('invoice', function ($query) {
                $query->where('client_id', $this->client_id);
            })
            ->with(['invoice.client', 'delivery'])
            ->get();
        $total_receivable = $receivables->where('is_paid', false)->sum('total');

        $pdf = Pdf::loadView('livewire.report.print-client-statement', [
            'client' => $client,
            'transactions' => $transactions,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'openingBalance' => $openingBalance,
            'finalBalance' => $finalBalance,
            'receivables' => $receivables,
            'total_receivable' => $total_receivable,
            'activeTab' => $this->activeTab,
            'date' => now(),
        ]);

        return response()->streamDownload(
            fn () => print $pdf->output(),
            "Situation_Client_" . str_replace(' ', '_', $client->nom) . "_" . now()->format('d_m_Y') . ".pdf"
        );
    }

    protected $listeners = ['update-client' => '$refresh'];

    public function render()
    {
        return view('livewire.report.client-statement-report', [
            'transactions' => $this->statementData,
            'client' => $this->client_id ? Client::find($this->client_id) : null,
        ])->layout('layouts.app');
    }
}
