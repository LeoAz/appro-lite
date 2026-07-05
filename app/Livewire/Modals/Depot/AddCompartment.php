<?php

namespace App\Livewire\Modals\Depot;

use App\Models\Compartment;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use LivewireUI\Modal\ModalComponent;

class AddCompartment extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public $depot_id;
    public ?array $data = [];

    public function mount($depot_id): void
    {
        $this->depot_id = $depot_id;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('product')
                    ->label('Produit')
                    ->required()
                    ->unique('compartments', 'product', modifyRuleUsing: function ($rule) {
                        return $rule->where('depot_id', $this->depot_id);
                    }, ignoreRecord: true),
                TextInput::make('capacity')
                    ->label('Capacité (Litres)')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                TextInput::make('quantity')
                    ->label('Quantité initiale (Litres)')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function create()
    {
        $state = $this->form->getState();
        $state['depot_id'] = $this->depot_id;

        Compartment::create($state);

        Notification::make()
            ->title('Compartiment ajouté')
            ->success()
            ->send();

        $this->dispatch('compartment-updated');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.modals.depot.add-compartment');
    }
}
