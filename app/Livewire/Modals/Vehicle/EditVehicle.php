<?php

namespace App\Livewire\Modals\Vehicle;

use App\Models\Vehicle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class EditVehicle extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Vehicle $vehicle;

    public $chassis;
    public $registration;
    public $carrier_id;
    public $driver;
    public $contact;
    public $capacity;

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
        $this->form->fill($vehicle->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                TextInput::make("chassis")->name("N° de chassis"),
                TextInput::make("registration")
                    ->name("N° de plaque")
                    ->required(),
                TextInput::make("capacity")->name("La capacité")->required(),
                Select::make("carrier_id")
                    ->label("Le transporteur")
                    ->relationship("carrier", "nom")
                    ->createOptionForm([
                        TextInput::make("nom")->name("Nom")->required(),
                        TextInput::make("contact")
                            ->label("Contact")
                            ->required(),
                        TextInput::make("address")
                            ->label("Adresse")
                            ->columnSpan(3),
                    ])
                    ->searchable()
                    ->preload()
                    ->required(),
                // ...
            ])
            //->statePath("data")
            ->model($this->vehicle);
    }

    public function update()
    {
        $this->customer->update($this->form->getState());
        Notification::make()
            ->title("Véhicule mise à jour")
            ->success()
            ->body("Le véhicule a été mise à jour avec succés!")
            ->send();

        $this->dispatch("update-vehicle");
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return "3xl";
    }

    public function render()
    {
        return view("livewire.modals.vehicle.edit-vehicle");
    }
}
