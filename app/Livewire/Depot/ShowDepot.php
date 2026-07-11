<?php

namespace App\Livewire\Depot;

use App\Models\Depot;
use App\Models\Compartment;
use App\Models\Load;
use App\Models\InvoiceItem;
use App\Models\DepotInvoiceItem;
use App\Models\FuelPurchase;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\Attributes\On;

use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ShowDepot extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Depot $depot;

    public function mount(Depot $depot)
    {
        $this->depot = $depot;
    }

    public function compartmentsTable(Table $table): Table
    {
        return $table
            ->query(Compartment::query()->where('depot_id', $this->depot->id))
            ->columns([
                TextColumn::make('product')
                    ->label('Produit')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Quantité en stock')
                    ->suffix(' L')
                    ->numeric(),
            ])
            ->headerActions([
                Action::make('addCompartment')
                    ->label('Ajouter un compartiment')
                    ->icon('heroicon-m-plus')
                    ->action(fn () => $this->dispatch('openModal', 'modals.depot.add-compartment', ['depot_id' => $this->depot->id])),
            ])
            ->actions([
                EditAction::make()
                    ->label('Modifier')
                    ->action(fn (Compartment $record) => $this->dispatch('openModal', 'modals.depot.edit-compartment', ['compartment' => $record])),
                DeleteAction::make()
                    ->label('Supprimer'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $this->compartmentsTable($table);
    }

    public function getCompartmentsTableProperty(): Table
    {
        return $this->compartmentsTable(new Table($this));
    }

    public function getSalesTableProperty(): Table
    {
        return $this->salesTable(new Table($this));
    }

    public function getPurchasesTableProperty(): Table
    {
        return $this->purchasesTable(new Table($this));
    }

    public function purchasesTable(Table $table): Table
    {
        return $table
            ->query(FuelPurchase::query()->where('depot_id', $this->depot->id))
            ->columns([
                TextColumn::make('purchase_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('product')
                    ->label('Produit')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Quantité')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' L')
                    ->numeric(),
                TextColumn::make('unit_price')
                    ->label('Prix Unitaire')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' FCFA')
                    ->numeric(),
                TextColumn::make('total_price')
                    ->label('Montant Total')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' FCFA')
                    ->numeric(),
            ])
            ->defaultSort('purchase_date', 'desc');
    }

    public function salesTable(Table $table): Table
    {
        $salesQuery = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('loads', 'loads.id', '=', 'invoice_items.load_id')
            ->where('loads.depot_id', $this->depot->id)
            ->select(
                'invoice_items.id as id',
                'invoices.number as reference',
                'invoices.date as date',
                'invoices.client_name as client_name',
                'invoice_items.total as total',
                'invoice_items.quantity_delivered as quantity',
                'loads.product as product',
                DB::raw("'Chargement' as type")
            )
            ->union(
                DB::table('depot_invoice_items')
                    ->join('depot_invoices', 'depot_invoices.id', '=', 'depot_invoice_items.depot_invoice_id')
                    ->leftJoin('clients', 'clients.id', '=', 'depot_invoices.client_id')
                    ->where('depot_invoices.depot_id', $this->depot->id)
                    ->select(
                        'depot_invoice_items.id as id',
                        'depot_invoices.number as reference',
                        'depot_invoices.date as date',
                        'clients.nom as client_name',
                        'depot_invoice_items.total as total',
                        'depot_invoice_items.quantity as quantity',
                        'depot_invoices.product as product',
                        DB::raw("'Direct Dépôt' as type")
                    )
            );

        return $table
            ->query(InvoiceItem::query()
                ->fromRaw("({$salesQuery->toSql()}) as sales_report")
                ->mergeBindings($salesQuery)
            )
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable(),
                TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable(),
                TextColumn::make('product')
                    ->label('Produit'),
                TextColumn::make('quantity')
                    ->label('Quantité')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' L')
                    ->numeric(),
                TextColumn::make('total')
                    ->label('Montant Total')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' FCFA')
                    ->numeric(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Chargement' => 'indigo',
                        'Direct Dépôt' => 'purple',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('date', 'desc');
    }

    #[On('compartment-updated')]
    public function refreshTable()
    {
        // Refresh the table
    }

    public function render()
    {
        $loads = Load::where('depot_id', $this->depot->id)
            ->latest()
            ->paginate(10);

        return view('livewire.depot.show-depot', [
            'loads' => $loads,
        ]);
    }
}
