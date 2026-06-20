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
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class EditLoad extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Load $load;

    public $load_date;
    public $load_location;
    public $capacity;
    public $product;
    public $vehicle_registration;

    public function mount(load $load): void
    {
        $this->load = $load;
        $this->form->fill($load->toArray());
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
                Select::make("load_location")
                    ->label("Lieu")
                    ->options(City::pluck("name", "name"))
                    ->searchable()
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

    public function update()
    {
        $this->load->update($this->form->getState());
        Notification::make()
            ->title("Chargement mise à jour")
            ->success()
            ->body("Le chargement a été mise à jour avec succés!")
            ->send();

        $this->dispatch("update-load");
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return "3xl";
    }

    public function render()
    {
        return view("livewire.modals.load.edit-load");
    }
}
