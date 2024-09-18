<?php

namespace App\Livewire\Modals\Load;

use App\Enums\VehicleStatus;
use App\Models\Depot;
use App\Models\Load;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AddLoad extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Vehicle $vehicle;
    public Depot $depot;
    public Load $load;

    public $load_date;
    public $load_location;
    public $depot_id;
    public $vehicle_id;
    public $capacity;
    public $product;

    public function mount(Load $load, Vehicle $vehicle, Depot $depot): void
    {
        $this->vehicle = $vehicle;
        $this->load = $load;
        $this->depot = $depot;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                DatePicker::make("load_date")
                    ->name("Date")
                    ->native(false)
                    ->displayFormat("d/m/Y")
                    ->default(now())
                    ->closeOnDateSelection()
                    ->required(),
                TextInput::make("load_location")->label("Lieu")->required(),
                Select::make("product")
                    ->label("Le produit")
                    ->options([
                        "Fuel" => "Fuel",
                        "Essence" => "Essence",
                        "Gasoil" => "Gasoil",
                    ])
                    ->required()
                    ->searchable(),

                Select::make("depot_id")
                    ->label("Le depot")
                    ->relationship("depot", "name")
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make("vehicle_id")
                    ->label("Le véhicule")
                    ->relationship("vehicle", "registration")
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make("capacity")
                    ->label("Nombre de litres")
                    ->required(),
            ])
            //->statePath("data");
            ->model($this->load);
    }

    public function create()
    {
        $attributes = $this->form->getState();
        Load::create($attributes);

        $vehicle = Vehicle::whereId($attributes["vehicle_id"])->first();
        $vehicle->update([
            "status" => VehicleStatus::Loaded,
        ]);

        Notification::make()
            ->title("Nouveau chargement ajouté")
            ->success()
            ->body("Le chargement a été enregistré avec succés!")
            ->send();

        $this->dispatch("add-load");
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return "3xl";
    }

    public function render()
    {
        return view("livewire.modals.load.add-load");
    }
}
