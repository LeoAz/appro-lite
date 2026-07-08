<?php

namespace App\Livewire\FuelPurchase;

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
use Livewire\Component;
use Livewire\Attributes\On;
use Barryvdh\DomPDF\Facade\Pdf;

class ListFuelPurchase extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function downloadPdf()
    {
        $purchases = $this->getTableQuery()->get();
        $pdf = Pdf::loadView('livewire.fuel-purchase.print-purchases', [
            'purchases' => $purchases,
        ]);

        return response()->streamDownload(
            fn () => print $pdf->output(),
            "Achats_carburant_" . now()->format('d_m_Y') . ".pdf"
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(FuelPurchase::query())
            ->defaultSort('purchase_date', 'desc')
            ->columns([
                TextColumn::make('purchase_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('depot.name')
                    ->label('Dépôt')
                    ->sortable(),
                TextColumn::make('product')
                    ->label('Produit'),
                TextColumn::make('quantity')
                    ->label('Quantité')
                    ->suffix(' L')
                    ->numeric(),
                TextColumn::make('unit_price')
                    ->label('Prix Unitaire')
                    ->money('XOF'),
                TextColumn::make('total_price')
                    ->label('Prix Total')
                    ->money('XOF'),
            ])
            ->headerActions([
                Action::make('addFuelPurchase')
                    ->label('Nouvel achat')
                    ->icon('heroicon-m-plus')
                    ->action(fn () => $this->dispatch('openModal', 'modals.fuel-purchase.add-fuel-purchase')),
            ])
            ->actions([
                EditAction::make()
                    ->label('Modifier')
                    ->action(fn (FuelPurchase $record) => $this->dispatch('openModal', 'modals.fuel-purchase.edit-fuel-purchase', ['fuelPurchase' => $record])),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->before(function (FuelPurchase $record) {
                        $compartment = $record->compartment;
                        if ($compartment) {
                            $compartment->decrement('quantity', $record->quantity);
                        }
                    }),
            ]);
    }

    #[On('fuel-purchase-updated')]
    public function refreshTable()
    {
        // Refresh the table
    }

    public function render()
    {
        return view('livewire.fuel-purchase.list-fuel-purchase');
    }
}
