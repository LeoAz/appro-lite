<?php

namespace App\Livewire\Depot;

use App\Models\Depot;
use App\Models\Compartment;
use App\Models\Load;
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

class ShowDepot extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public Depot $depot;

    public function mount(Depot $depot)
    {
        $this->depot = $depot;
    }

    public function table(Table $table): Table
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
