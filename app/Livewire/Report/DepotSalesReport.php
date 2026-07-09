<?php

namespace App\Livewire\Report;

use App\Models\DepotInvoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class DepotSalesReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $date_from;
    public $date_to;
    public $client_id;
    public $depot_id;

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date_from')
                    ->label('Du')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                DatePicker::make('date_to')
                    ->label('Au')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
                Select::make('client_id')
                    ->label('Client')
                    ->options(\App\Models\Client::pluck('nom', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable())
                    ->placeholder('Tous les clients'),
                Select::make('depot_id')
                    ->label('Dépôt')
                    ->options(\App\Models\Depot::pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable())
                    ->placeholder('Tous les dépôts'),
            ])
            ->columns(4);
    }

    public function table(Table $table): Table
    {
        $query = DepotInvoice::query()
            ->when($this->date_from, fn ($q) => $q->whereDate('date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('date', '<=', $this->date_to))
            ->when($this->client_id, fn ($q) => $q->where('client_id', $this->client_id))
            ->when($this->depot_id, fn ($q) => $q->where('depot_id', $this->depot_id));

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('number')
                    ->label('N° Facture')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('depot.name')
                    ->label('Dépôt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product')
                    ->label('Produit'),
                TextColumn::make('total_amount')
                    ->label('Montant Total')
                    ->suffix(' FCFA')
                    ->numeric()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total')),
            ])
            ->defaultSort('date', 'desc');
    }

    public function downloadPdf()
    {
        $query = DepotInvoice::query()
            ->when($this->date_from, fn ($q) => $q->whereDate('date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('date', '<=', $this->date_to))
            ->when($this->client_id, fn ($q) => $q->where('client_id', $this->client_id))
            ->when($this->depot_id, fn ($q) => $q->where('depot_id', $this->depot_id))
            ->orderBy('date', 'desc');

        $invoices = $query->get();
        $total_amount = $invoices->sum('total_amount');

        $pdf = Pdf::loadView('livewire.report.print-depot-sales', [
            'invoices' => $invoices,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'client_id' => $this->client_id,
            'depot_id' => $this->depot_id,
            'total_amount' => $total_amount,
            'date' => now(),
        ]);

        return response()->streamDownload(
            fn () => print $pdf->output(),
            "Rapport_de_vente_depot_" . now()->format('d_m_Y') . ".pdf"
        );
    }

    public function render()
    {
        return view('livewire.report.depot-sales-report');
    }
}
