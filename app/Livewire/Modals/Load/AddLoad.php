<?php

namespace App\Livewire\Modals\Load;

use App\Models\City;
use App\Models\Load;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use LivewireUI\Modal\ModalComponent;

class AddLoad extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Load $load;

    public $load_date;
    public $load_location;
    public $capacity;
    public $product;
    public $vehicle_registration;

    public function mount(?Load $load = null): void
    {
        $this->load = $load ?? new Load();
        $this->form->fill([
            'load_date' => now(),
        ]);
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
                Select::make("product")
                    ->label("Le produit")
                    ->options([
                        "FUEL" => "FUEL",
                        "SUPER" => "SUPER",
                        "GASOIL" => "GASOIL",
                    ])
                    ->required()
                    ->searchable(),

                Select::make("load_location")
                    ->label("Le lieux")
                    ->options(City::pluck("name", "name"))
                    ->searchable()
                    ->required(),

                TextInput::make("vehicle_registration")
                    ->label("Le véhicule")
                    ->placeholder("Saisir l'immatriculation")
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
        return "5xl";
    }

    public function render()
    {
        return view("livewire.modals.load.add-load");
    }
}
