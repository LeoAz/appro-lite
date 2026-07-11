<?php

namespace App\Livewire\Modals\Load;

use App\Models\City;
use App\Models\Client;
use App\Models\Load;
use App\Models\Depot;
use App\Enums\LoadStatus;
use App\Models\Compartment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class EditLoad extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Load $load;

    public $load_date;
    public $load_location;
    public $unload_date;
    public $unload_location;
    public $client_id;
    public $client_name;
    public $volume;
    public $product;
    public $vehicle_registration;
    public $depot_id;
    public $compartment_id;

    public function mount(load $load): void
    {
        $this->load = $load;
        $this->form->fill($load->toArray());
        $this->client_id = $load->client_id;
        $this->client_name = $load->client_name;
    }

    public function form(Form $form): Form
    {
        $isLivre = in_array($this->load->status, [LoadStatus::Unloaded, LoadStatus::Invoiced]);

        return $form
            ->columns(2)
            ->schema([
                DatePicker::make($isLivre ? "unload_date" : "load_date")
                    ->label($isLivre ? "Date Livraison" : "Date Chargement")
                    ->native(false)
                    ->displayFormat("d/m/Y")
                    ->closeOnDateSelection()
                    ->required(),
                $isLivre ?
                    TextInput::make("unload_location")
                        ->label("Lieu Livraison")
                        ->required()
                    :
                    Select::make("load_location")
                        ->label("Lieu Chargement")
                        ->options(City::pluck("name", "name"))
                        ->searchable()
                        ->required(),
                Select::make("client_id")
                    ->label("Client")
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
                    ->hidden(!$isLivre)
                    ->required($isLivre)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, $state) => $set('client_name', Client::find($state)?->nom)),
                \Filament\Forms\Components\Hidden::make('client_name'),

                Select::make("depot_id")
                    ->label("Dépôt")
                    ->options(Depot::all()->pluck('name', 'id'))
                    ->required()
                    ->live(),
                Select::make("compartment_id")
                    ->label("Produit (Compartiment)")
                    ->options(fn (Get $get) => Compartment::where('depot_id', $get('depot_id'))
                        ->get()
                        ->mapWithKeys(fn ($c) => [$c->id => "{$c->product} (Dispo: {$c->quantity} L)"]))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, $state) => $set('product', Compartment::find($state)?->product)),

                TextInput::make("product")
                    ->label("Produit")
                    ->disabled()
                    ->dehydrated(),

                TextInput::make("vehicle_registration")
                    ->label("Le véhicule")
                    ->placeholder("Saisir l'immatriculation")
                    ->required(),

                TextInput::make("volume")
                    ->label("Nombre de litres")
                    ->numeric()
                    ->required(),
            ])
            ->model($this->load);
    }

    public function update()
    {
        $data = $this->form->getState();

        // Nettoyage du volume
        if (isset($data['volume'])) {
            $data['volume'] = (int) str_replace([' ', ','], '', $data['volume']);
        }

        $oldVolume = $this->load->volume;
        $oldCompartmentId = $this->load->compartment_id;
        $newVolume = $data['volume'];
        $newCompartmentId = $data['compartment_id'];

        $compartment = Compartment::find($newCompartmentId);

        // Vérification du stock (uniquement si pas encore livré/facturé)
        $isLivreOuFacture = in_array($this->load->status, [LoadStatus::Unloaded, LoadStatus::Invoiced]);
        if (!$isLivreOuFacture) {
            // Si c'est le même compartiment, on vérifie si la nouvelle quantité est possible
            // (dispo actuelle + ancienne quantité réservée >= nouvelle quantité)
            if ($oldCompartmentId == $newCompartmentId) {
                if (($compartment->quantity + $oldVolume) < $newVolume) {
                    Notification::make()
                        ->title("Stock insuffisant")
                        ->danger()
                        ->body("La quantité demandée ({$newVolume} L) dépasse le stock disponible avec l'ajustement.")
                        ->send();
                    return;
                }
            } else {
                // Si le compartiment change, on vérifie simplement le nouveau stock
                if ($compartment->quantity < $newVolume) {
                    Notification::make()
                        ->title("Stock insuffisant")
                        ->danger()
                        ->body("Le nouveau compartiment n'a pas assez de stock ({$compartment->quantity} L).")
                        ->send();
                    return;
                }
            }
        }

        DB::transaction(function () use ($data, $oldVolume, $oldCompartmentId, $newVolume, $newCompartmentId) {
            // Ne pas modifier le stock si c'est déjà facturé ou livré (déjà déduit lors de la création/chargement)
            $isLivreOuFacture = in_array($this->load->status, [LoadStatus::Unloaded, LoadStatus::Invoiced]);

            if (!$isLivreOuFacture) {
                // Remettre l'ancien stock
                if ($oldCompartmentId) {
                    Compartment::find($oldCompartmentId)?->increment('quantity', $oldVolume);
                }

                // Déduire le nouveau stock
                Compartment::find($newCompartmentId)->decrement('quantity', $newVolume);
            }

            // Mettre à jour le chargement
            $this->load->update($data);
        });

        Notification::make()
            ->title(in_array($this->load->status, [LoadStatus::Unloaded, LoadStatus::Invoiced]) ? "Livraison mise à jour" : "Chargement mise à jour")
            ->success()
            ->body(in_array($this->load->status, [LoadStatus::Unloaded, LoadStatus::Invoiced]) ? "La livraison a été mise à jour avec succès!" : "Le chargement a été mise à jour avec succés!")
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
