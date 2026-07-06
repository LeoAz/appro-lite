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

class AddFuelPurchase extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'purchase_date' => now(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('purchase_date')
                    ->label('Date d\'achat')
                    ->required()
                    ->default(now()),
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
                        $unitPrice = $get('unit_price');
                        if ($unitPrice !== null && $unitPrice !== '') {
                            $set('total_price', $quantity * (float) $unitPrice);
                        } else {
                            $set('total_price', null);
                        }
                    }),
                TextInput::make('unit_price')
                    ->label('Prix Unitaire')
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $quantity = (float) $get('quantity');
                        $unitPrice = $get('unit_price');
                        if ($unitPrice !== null && $unitPrice !== '') {
                            $set('total_price', $quantity * (float) $unitPrice);
                        } else {
                            $set('total_price', null);
                        }
                    }),
                TextInput::make('total_price')
                    ->label('Prix Total')
                    ->numeric()
                    ->readOnly(),
            ])
            ->statePath('data');
    }

    public function create()
    {
        $state = $this->form->getState();

        DB::transaction(function () use ($state) {
            FuelPurchase::create($state);

            // Update compartment quantity
            $compartment = Compartment::find($state['compartment_id']);
            $compartment->increment('quantity', $state['quantity']);
        });

        Notification::make()
            ->title('Achat enregistré')
            ->success()
            ->send();

        $this->dispatch('fuel-purchase-updated');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.modals.fuel-purchase.add-fuel-purchase');
    }
}
