<?php

namespace App\Livewire\Modals\Vehicle;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AddVehicle extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Vehicle $vehicle;

    public $chassis;
    public $registration;
    public $carrier_id;
    public $driver;
    public $contact;
    public $capacity;

    public function mount(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
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
                            ->columnSpan(2),
                    ])
                    ->searchable()
                    ->preload()
                    ->required(),
                // ...
            ])
            //->statePath("data")
            ->model($this->vehicle);
    }

    public function create()
    {
        $attributes = $this->form->getState();
        $attributes = [...$attributes, "status" => VehicleStatus::Available];
        //dd($attributes);
        Vehicle::create($attributes);
        Notification::make()
            ->title("Nouveau vehicule ajouté")
            ->success()
            ->body("Le véhicule a été enregistré avec succés!")
            ->send();

        $this->dispatch("add-vehicle");
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return "3xl";
    }

    public function render()
    {
        return view("livewire.modals.vehicle.add-vehicle");
    }
}
