<?php

namespace App\Livewire\Report;

use App\Models\Load;
use App\Models\FuelPurchase;
use App\Models\Depot;
use App\Models\Compartment;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class StockReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $depot_id = null;

    public function mount()
    {
        $this->depot_id = Depot::first()?->id;
        $this->form->fill([
            'depot_id' => $this->depot_id
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('depot_id')
                    ->hiddenLabel()
                    ->searchable()
                    ->options(Depot::all()->pluck('name', 'id'))
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable())
                    ->placeholder('Choisir un dépôt'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Compartment::query()
                    ->when($this->depot_id, fn ($query) => $query->where('depot_id', $this->depot_id))
            )
            ->columns([
                TextColumn::make('product')
                    ->label('Produit')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Stock actuel')
                    ->suffix(' L')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('capacity')
                    ->label('Capacité totale')
                    ->suffix(' L')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('État')
                    ->state(function (Compartment $record): string {
                        $percentage = $record->capacity > 0 ? ($record->quantity / $record->capacity) * 100 : 0;
                        if ($percentage < 10) return 'Critique';
                        if ($percentage < 30) return 'Bas';
                        return 'Normal';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Critique' => 'danger',
                        'Bas' => 'warning',
                        default => 'success',
                    }),
            ])
            ->paginated(false);
    }

    public function getLoadTableQuery()
    {
        return Load::query()
            ->when($this->depot_id, fn ($query) => $query->where('depot_id', $this->depot_id))
            ->latest();
    }

    public function getPurchaseTableQuery()
    {
        return FuelPurchase::query()
            ->when($this->depot_id, fn ($query) => $query->where('depot_id', $this->depot_id))
            ->latest();
    }

    public function downloadPdf()
    {
        $selectedDepot = $this->depot_id ? Depot::find($this->depot_id) : null;
        $compartments = Compartment::query()
            ->when($this->depot_id, fn ($query) => $query->where('depot_id', $this->depot_id))
            ->get();

        $pdf = Pdf::loadView('livewire.report.print-stock', [
            'compartments' => $compartments,
            'selectedDepot' => $selectedDepot,
            'date' => now(),
        ]);

        return response()->streamDownload(
            fn () => print $pdf->output(),
            "Rapport_de_stock_" . now()->format('d_m_Y') . ".pdf"
        );
    }

    public function render()
    {
        return view('livewire.report.stock-report');
    }
}
