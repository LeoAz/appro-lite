<?php

namespace App\Livewire\Report;

use App\Models\Depot;
use App\Models\Compartment;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class StockReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Compartment::query())
            ->columns([
                TextColumn::make('depot.name')
                    ->label('Dépôt')
                    ->sortable()
                    ->searchable(),
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
            ->filters([
                SelectFilter::make('depot_id')
                    ->label('Dépôt')
                    ->options(Depot::all()->pluck('name', 'id')),
            ])
            ->paginated(false);
    }

    public function downloadPdf()
    {
        $compartments = $this->getTableQuery()->get();
        $pdf = Pdf::loadView('livewire.report.print-stock', [
            'compartments' => $compartments,
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
