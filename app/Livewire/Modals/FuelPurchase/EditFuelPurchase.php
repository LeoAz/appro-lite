<?php

namespace App\Livewire\Modals\FuelPurchase;

use App\Models\FuelPurchase;
use App\Models\Depot;
use App\Models\Compartment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\DB;

class EditFuelPurchase extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public FuelPurchase $fuelPurchase;
    public ?array $data = [];

    public function mount(FuelPurchase $fuelPurchase): void
    {
        $this->fuelPurchase = $fuelPurchase;
        $this->form->fill($fuelPurchase->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('purchase_date')
                    ->label('Date d\'achat')
                    ->required(),
                Select::make('depot_id')
                    ->label('Dépôt')
                    ->options(Depot::all()->pluck('name', 'id'))
                    ->required()
                    ->live(),
                Select::make('compartment_id')
                    ->label('Compartiment / Produit')
                    ->options(fn (Get $get) => Compartment::where('depot_id', $get('depot_id'))->pluck('product', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, $state) => $set('product', Compartment::find($state)?->product)),
                TextInput::make('product')
                    ->label('Produit')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('quantity')
                    ->label('Quantité (Litres)')
                    ->numeric()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $quantity = (float) $get('quantity');
                        $unitPrice = (float) $get('unit_price');
                        $set('total_price', $quantity * $unitPrice);
                    }),
                TextInput::make('unit_price')
                    ->label('Prix Unitaire')
                    ->numeric()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $quantity = (float) $get('quantity');
                        $unitPrice = (float) $get('unit_price');
                        $set('total_price', $quantity * $unitPrice);
                    }),
                TextInput::make('total_price')
                    ->label('Prix Total')
                    ->numeric()
                    ->required()
                    ->readOnly(),
            ])
            ->statePath('data');
    }

    public function update()
    {
        $state = $this->form->getState();
        $oldQuantity = $this->fuelPurchase->quantity;
        $oldCompartmentId = $this->fuelPurchase->compartment_id;

        DB::transaction(function () use ($state, $oldQuantity, $oldCompartmentId) {
            // Revert old quantity
            $oldCompartment = Compartment::find($oldCompartmentId);
            if ($oldCompartment) {
                $oldCompartment->decrement('quantity', $oldQuantity);
            }

            // Update purchase
            $this->fuelPurchase->update($state);

            // Add new quantity
            $newCompartment = Compartment::find($state['compartment_id']);
            $newCompartment->increment('quantity', $state['quantity']);
        });

        Notification::make()
            ->title('Achat mis à jour')
            ->success()
            ->send();

        $this->dispatch('fuel-purchase-updated');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.modals.fuel-purchase.edit-fuel-purchase');
    }
}
