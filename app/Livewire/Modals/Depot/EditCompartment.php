<?php

namespace App\Livewire\Modals\Depot;

use App\Models\Compartment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use LivewireUI\Modal\ModalComponent;

class EditCompartment extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Compartment $compartment;
    public ?array $data = [];

    public function mount(Compartment $compartment): void
    {
        $this->compartment = $compartment;
        $this->form->fill($compartment->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product')
                    ->label('Produit')
                    ->options([
                        'FUEL' => 'FUEL',
                        'SUPER' => 'SUPER',
                        'GASOIL' => 'GASOIL',
                    ])
                    ->required()
                    ->unique('compartments', 'product', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where('depot_id', $this->compartment->depot_id);
                    }),
                TextInput::make('quantity')
                    ->label('Quantité actuelle (Litres)')
                    ->numeric()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function update()
    {
        $this->compartment->update($this->form->getState());

        Notification::make()
            ->title('Compartiment mis à jour')
            ->success()
            ->send();

        $this->dispatch('compartment-updated');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.modals.depot.edit-compartment');
    }
}
