<?php

namespace App\Livewire\Modals\Load;

use App\Enums\LoadStatus;
use App\Enums\VehicleStatus;
use App\Models\Client;
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

class AddUnload extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Load $load;
    public Client $client;

    public $unload_date;
    public $unload_location;
    public $client_id;

    public function mount(load $load, Client $client): void
    {
        $this->load = $load;
        $this->client = $client;
        $this->form->fill($load->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                DatePicker::make("unload_date")
                    ->name("Date")
                    ->native(false)
                    ->displayFormat("d/m/Y")
                    ->default(now())
                    ->closeOnDateSelection()
                    ->required(),
                TextInput::make("unload_location")
                    ->label("Lieu déchargement")
                    ->required(),
                Select::make("client_id")
                    ->label("Le client")
                    ->relationship("client", "nom")
                    ->createOptionForm([
                        TextInput::make("nom")->name("Nom")->required(),
                    ])
                    ->searchable()
                    ->preload()
                    ->required(),
            ])
            //->statePath("data");
            ->model($this->load);
    }

    public function unload()
    {
        $attributes = $this->form->getState();
        $attributes = [
            ...$attributes,
            "status" => LoadStatus::Unloaded,
            "is_unload" => true,
        ];
        $vehicle = Vehicle::whereId($this->load->vehicle_id)->first();
        $vehicle->update([
            "status" => VehicleStatus::Available,
        ]);

        $this->load->update($attributes);
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
        return "5xl";
    }

    public function render()
    {
        return view("livewire.modals.load.add-unload");
    }
}
