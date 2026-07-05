<?php

namespace App\Livewire\Modals\Load;

use App\Models\City;
use App\Models\Load;
use App\Models\Depot;
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
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Actions\Action;

class AddLoad extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Load $load;
    public $depot_id;
    public $compartment_id;
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
                    ->label("Produit sélectionné")
                    ->disabled()
                    ->dehydrated(),
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
                    ->numeric()
                    ->required(),
            ])
            ->model($this->load);
    }

    public function create()
    {
        $attributes = $this->form->getState();
        $compartment = Compartment::find($attributes['compartment_id']);

        if ($compartment->quantity < $attributes['capacity']) {
            Notification::make()
                ->title("Stock insuffisant")
                ->danger()
                ->body("La quantité chargée ({$attributes['capacity']} L) est supérieure à la quantité disponible dans le dépôt ({$compartment->quantity} L).")
                ->send();
            return;
        }

        DB::transaction(function () use ($attributes, $compartment) {
            Load::create($attributes);
            $compartment->decrement('quantity', $attributes['capacity']);
        });

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
