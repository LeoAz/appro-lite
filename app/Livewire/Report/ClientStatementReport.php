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
use App\Models\DepotInvoiceItem;
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
                    ->select(
                        'invoice_items.id',
                        'invoice_items.invoice_id',
                        'invoice_items.load_id',
                        'invoice_items.total',
                        'invoice_items.is_paid',
                        'invoice_items.client_payment_id',
                        \DB::raw('NULL as depot_invoice_id'),
                        \DB::raw('NULL as compartment_id'),
                        \DB::raw('invoice_items.quantity_delivered as quantity'),
                        \DB::raw("'load' as item_type"),
                        \DB::raw("(select date from invoices where invoices.id = invoice_items.invoice_id limit 1) as item_date")
                    )
                    ->where(function ($query) {
                        $clientId = $this->client_id;
                        if ($clientId) {
                            $query->where(function($q) use ($clientId) {
                                $q->whereExists(fn($eq) => $eq->select(\DB::raw(1))->from('invoices')->whereColumn('invoices.id', 'invoice_items.invoice_id')->where('invoices.client_id', $clientId))
                                  ->orWhereExists(fn($eq) => $eq->select(\DB::raw(1))->from('loads')->whereColumn('loads.id', 'invoice_items.load_id')->where('loads.client_id', $clientId));
                            });
                        }

                        if ($this->activeTab === 'receivables') {
                            $query->where('invoice_items.is_paid', false)
                                ->where(function($sq) {
                                    $sq->whereExists(function($q) {
                                        $q->select(\DB::raw(1))
                                            ->from('loads')
                                            ->whereColumn('loads.id', 'invoice_items.load_id')
                                            ->whereIn('loads.status', [\App\Enums\LoadStatus::Unloaded, \App\Enums\LoadStatus::Invoiced]);
                                    })->orWhereNull('invoice_items.load_id');
                                });
                        } elseif ($this->activeTab === 'payment_history') {
                            $query->where('invoice_items.is_paid', true);
                        }
                    })
                    ->union(
                        DepotInvoiceItem::query()
                            ->select(
                                'depot_invoice_items.id',
                                \DB::raw('NULL as invoice_id'),
                                \DB::raw('NULL as load_id'),
                                'depot_invoice_items.total',
                                'depot_invoice_items.is_paid',
                                'depot_invoice_items.client_payment_id',
                                'depot_invoice_items.depot_invoice_id',
                                'depot_invoice_items.compartment_id',
                                'depot_invoice_items.quantity',
                                \DB::raw("'depot' as item_type"),
                                \DB::raw("(select date from depot_invoices where depot_invoices.id = depot_invoice_items.depot_invoice_id limit 1) as item_date")
                            )
                            ->where(function ($query) {
                                $clientId = $this->client_id;
                                if ($clientId) {
                                    $query->whereExists(fn($eq) => $eq->select(\DB::raw(1))->from('depot_invoices')->whereColumn('depot_invoices.id', 'depot_invoice_items.depot_invoice_id')->where('depot_invoices.client_id', $clientId));
                                }

                                if ($this->activeTab === 'receivables') {
                                    $query->where('depot_invoice_items.is_paid', false);
                                } elseif ($this->activeTab === 'payment_history') {
                                    $query->where('depot_invoice_items.is_paid', true);
                                }
                            })
                    )
                    ->when($this->activeTab === 'statement', function($query) {
                        // Pas de filtre is_paid spécifique pour l'onglet relevé global si besoin,
                        // mais ici on garde la logique existante.
                    })
                    ->with(['invoice.client', 'delivery', 'payment', 'depotInvoice.client', 'compartment'])
            )
            ->columns([
                TextColumn::make('item_type')
                    ->label('Type')
                    ->formatStateUsing(fn($state) => $state === 'load' ? 'Livraison' : 'Dépôt')
                    ->badge()
                    ->color(fn($state) => $state === 'load' ? 'info' : 'warning'),
                TextColumn::make('invoice.number')
                    ->label('N° Facture')
                    ->getStateUsing(fn($record) => $record->item_type === 'depot' ? ($record->depotInvoice?->number ?? '-') : ($record->invoice?->number ?? '-'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('item_date')
                    ->label('Date Facture')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('invoice.client.nom')
                    ->label('Client')
                    ->getStateUsing(fn($record) => $record->item_type === 'depot' ? ($record->depotInvoice?->client?->nom ?? '-') : ($record->invoice?->client?->nom ?? '-'))
                    ->sortable()
                    ->searchable()
                    ->hidden(fn () => !empty($this->client_id)),
                TextColumn::make('delivery.vehicle_registration')
                    ->label('Véhicule / Dépôt')
                    ->getStateUsing(fn($record) => $record->item_type === 'depot' ? ($record->depotInvoice?->depot?->name ?? '-') : ($record->delivery?->vehicle_registration ?? '-'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('delivery.product')
                    ->label('Produit')
                    ->getStateUsing(fn($record) => $record->item_type === 'depot' ? ($record->compartment?->product ?? '-') : ($record->delivery?->product ?? '-')),
                TextColumn::make('quantity')
                    ->label('Qté Facturée')
                    ->numeric()
                    ->suffix(' L')
                    ->summarize(Sum::make()->label('Total Qty')),
                TextColumn::make('total')
                    ->label(fn() => $this->activeTab === 'receivables' ? 'Montant Dû' : 'Montant Payé')
                    ->numeric()
                    ->suffix(' FCFA')
                    ->summarize(Sum::make()->label('Total')->money('XOF')),
                TextColumn::make('payment.date')
                    ->label('Date Paiement')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('-')
                    ->visible(fn() => $this->activeTab === 'payment_history'),
                TextColumn::make('payment.reference')
                    ->label('Réf. Règlement')
                    ->searchable()
                    ->placeholder('-')
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
            ->defaultSort('item_date', 'asc')
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

                // Détailler les items payés par ce règlement
                $itemsPaid = $payment->invoiceItems()->with('delivery')->get();
                if ($itemsPaid->count() > 0) {
                    $details = $itemsPaid->map(function($item) {
                        return $item->delivery ? "{$item->delivery->vehicle_registration} ({$item->quantity_delivered}L)" : "Item #{$item->id}";
                    })->implode(', ');
                    $operation .= " - Payé: " . $details;
                }
            }

            // Si c'est un règlement sur dépôt, mentionner les factures concernées
            if (!$payment->is_advance && $payment->payment_type === 'depot') {
                $depotInvoices = $payment->depotInvoiceItems()
                    ->with('depotInvoice')
                    ->get()
                    ->map(fn($item) => $item->depotInvoice?->number)
                    ->filter()
                    ->unique()
                    ->implode(', ');

                if ($depotInvoices) {
                    $operation .= " [Factures: {$depotInvoices}]";
                }

                $itemsPaid = $payment->depotInvoiceItems()->with('compartment')->get();
                if ($itemsPaid->count() > 0) {
                    $details = $itemsPaid->map(function($item) {
                        return "{$item->compartment?->product} ({$item->quantity}L)";
                    })->implode(', ');
                    $operation .= " - Payé: " . $details;
                }
            }

            if ($payment->payment_method) {
                $operation .= " ({$payment->payment_method})";
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
            ->select(
                'invoice_items.id',
                'invoice_items.invoice_id',
                'invoice_items.load_id',
                'invoice_items.total',
                'invoice_items.is_paid',
                'invoice_items.client_payment_id',
                \DB::raw('NULL as depot_invoice_id'),
                \DB::raw('NULL as compartment_id'),
                \DB::raw('invoice_items.quantity_delivered as quantity'),
                \DB::raw("'load' as item_type"),
                \DB::raw("(select date from invoices where invoices.id = invoice_items.invoice_id limit 1) as item_date")
            )
            ->where(function ($query) use ($client) {
                $clientId = $client->id;
                $query->whereExists(fn($eq) => $eq->select(\DB::raw(1))->from('invoices')->whereColumn('invoices.id', 'invoice_items.invoice_id')->where('invoices.client_id', $clientId))
                      ->orWhereExists(fn($eq) => $eq->select(\DB::raw(1))->from('loads')->whereColumn('loads.id', 'invoice_items.load_id')->where('loads.client_id', $clientId));
            })
            ->where('invoice_items.is_paid', false)
            ->where(function($sq) {
                $sq->whereExists(function($q) {
                    $q->select(\DB::raw(1))
                        ->from('loads')
                        ->whereColumn('loads.id', 'invoice_items.load_id')
                        ->whereIn('loads.status', [\App\Enums\LoadStatus::Unloaded, \App\Enums\LoadStatus::Invoiced]);
                })->orWhereNull('invoice_items.load_id');
            })
            ->union(
                DepotInvoiceItem::query()
                    ->select(
                        'depot_invoice_items.id',
                        \DB::raw('NULL as invoice_id'),
                        \DB::raw('NULL as load_id'),
                        'depot_invoice_items.total',
                        'depot_invoice_items.is_paid',
                        'depot_invoice_items.client_payment_id',
                        'depot_invoice_items.depot_invoice_id',
                        'depot_invoice_items.compartment_id',
                        'depot_invoice_items.quantity',
                        \DB::raw("'depot' as item_type"),
                        \DB::raw("(select date from depot_invoices where depot_invoices.id = depot_invoice_items.depot_invoice_id limit 1) as item_date")
                    )
                    ->whereExists(fn($eq) => $eq->select(\DB::raw(1))->from('depot_invoices')->whereColumn('depot_invoices.id', 'depot_invoice_items.depot_invoice_id')->where('depot_invoices.client_id', $client->id))
                    ->where('depot_invoice_items.is_paid', false)
            )
            ->with(['invoice.client', 'delivery', 'payment', 'depotInvoice.client', 'compartment', 'depotInvoice.depot'])
            ->get();
        $total_receivable = $receivables->sum('total');

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
