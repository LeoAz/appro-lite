<?php

namespace App\Livewire\Modals\Load;

use App\Models\City;
use App\Models\Client;
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
        $isLivre = $this->load->status === 'LIVRÉ';

        return $form
            ->columns(2)
            ->schema([
                DatePicker::make($isLivre ? "unload_date" : "load_date")
                    ->label($isLivre ? "Date Livraison" : "Date Chargement")
                    ->native(false)
                    ->displayFormat("d/m/Y")
                    ->default(now())
                    ->closeOnDateSelection()
                    ->required(),
                Select::make($isLivre ? "unload_location" : "load_location")
                    ->label($isLivre ? "Lieu Livraison" : "Lieu Chargement")
                    ->options(City::pluck("name", "name"))
                    ->searchable()
                    ->required(),
                Select::make("client_id")
                    ->label("Client")
                    ->options(\App\Models\Client::pluck("nom", "id"))
                    ->searchable()
                    ->hidden(!$isLivre)
                    ->required($isLivre),
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
        $data = $this->form->getState();

        if (isset($data['client_id'])) {
            $client = \App\Models\Client::find($data['client_id']);
            if ($client) {
                $data['client_name'] = $client->nom;
            }
        }

        $this->load->update($data);
        Notification::make()
            ->title($this->load->status === 'LIVRÉ' ? "Livraison mise à jour" : "Chargement mise à jour")
            ->success()
            ->body($this->load->status === 'LIVRÉ' ? "La livraison a été mise à jour avec succès!" : "Le chargement a été mise à jour avec succés!")
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
