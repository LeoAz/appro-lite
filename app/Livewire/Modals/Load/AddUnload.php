<?php

namespace App\Livewire\Modals\Load;

use App\Enums\LoadStatus;
use App\Models\Client;
use App\Models\Load;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use LivewireUI\Modal\ModalComponent;

class AddUnload extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Load $load;

    public $unload_date;
    public $unload_location;
    public $client_id;
    public $client_name;
    public $status;
    public $is_unload;

    public function mount(?Load $load = null): void
    {
        $this->load = $load ?? new Load();

        $data = $this->load->toArray();
        if (empty($data['unload_date'])) {
            $data['unload_date'] = now();
        }

        $this->form->fill($data);
        $this->client_id = $this->load->client_id;
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
                    ->label("Lieu livraison")
                    ->required(),
                \Filament\Forms\Components\Select::make("client_id")
                    ->label("Le client")
                    ->options(Client::pluck("nom", "id"))
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('nom')
                            ->label('Nom du client')
                            ->required(),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return Client::create(['nom' => $data['nom']])->id;
                    })
                    ->getOptionLabelUsing(fn ($value): ?string => Client::find($value)?->nom)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, $state) => $set('client_name', Client::find($state)?->nom)),
                \Filament\Forms\Components\Hidden::make('client_name'),
            ])
            //->statePath("data");
            ->model($this->load);
    }

    public function unload()
    {
        $attributes = $this->form->getState();

        // Nettoyage du volume au cas où il serait modifié/affiché (bien qu'il soit sur l'objet load ici)
        if (isset($attributes['volume'])) {
            $attributes['volume'] = (int) str_replace([' ', ','], '', $attributes['volume']);
        }

        $attributes = [
            ...$attributes,
            "status" => LoadStatus::Unloaded,
            "is_unload" => true,
        ];

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
