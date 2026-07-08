<?php

namespace App\Livewire\Report;

use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $date_from;
    public $date_to;
    public $client_id;
    public $group_by_client = false;

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
                Select::make('group_by_client')
                    ->label('Regrouper par')
                    ->options([
                        false => 'Pas de regroupement',
                        true => 'Client',
                    ])
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ])
            ->columns(4);
    }

    public function table(Table $table): Table
    {
        $query = Invoice::query()
            ->when($this->date_from, fn ($q) => $q->whereDate('date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('date', '<=', $this->date_to))
            ->when($this->client_id, fn ($q) => $q->where('client_id', $this->client_id));

        if ($this->group_by_client) {
            $query->orderBy('client_id');
        }

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
                    ->sortable()
                    ->visible(!$this->group_by_client),
                TextColumn::make('total_missing')
                    ->label('Total Manquant')
                    ->suffix(' L')
                    ->numeric()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total')),
                TextColumn::make('total_amount')
                    ->label('Montant Total')
                    ->suffix(' FCFA')
                    ->numeric()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total')),
            ])
            ->groups($this->group_by_client ? ['client_id'] : [])
            ->defaultSort('date', 'desc');
    }

    public function downloadPdf()
    {
        $query = Invoice::query()
            ->when($this->date_from, fn ($q) => $q->whereDate('date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('date', '<=', $this->date_to))
            ->when($this->client_id, fn ($q) => $q->where('client_id', $this->client_id))
            ->orderBy($this->group_by_client ? 'client_id' : 'date', $this->group_by_client ? 'asc' : 'desc');

        $invoices = $query->get();
        $total_missing = $invoices->sum('total_missing');
        $total_amount = $invoices->sum('total_amount');

        $pdf = Pdf::loadView('livewire.report.print-sales', [
            'invoices' => $invoices,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'client_id' => $this->client_id,
            'group_by_client' => $this->group_by_client,
            'total_missing' => $total_missing,
            'total_amount' => $total_amount,
            'date' => now(),
        ]);

        return response()->streamDownload(
            fn () => print $pdf->output(),
            "Rapport_de_vente_" . now()->format('d_m_Y') . ".pdf"
        );
    }

    public function render()
    {
        return view('livewire.report.sales-report');
    }
}
