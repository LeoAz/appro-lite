<?php

namespace App\Livewire\Modals\DepotInvoice;

use App\Models\Client;
use App\Models\Depot;
use App\Models\Compartment;
use App\Models\DepotInvoice;
use App\Models\DepotInvoiceItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
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

class AddDepotInvoice extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public function model(): string
    {
        return DepotInvoice::class;
    }

    public $number;
    public $date;
    public $client_id;
    public $depot_id;
    public $product;
    public $issuer_name = 'CORRIDOR PETROLEUM';
    public $items = [];
    public $total_amount = 0;

    public function mount(): void
    {
        $lastInvoice = DepotInvoice::whereYear('date', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice && preg_match('/FAC-DEP-\d{4}-(\d{5})/', $lastInvoice->number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }

        $number = 'FAC-DEP-' . date('Y') . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        $this->form->fill([
            'date' => now(),
            'number' => $number,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(DepotInvoice::class)
            ->schema([
                TextInput::make('number')
                    ->label('Numéro de facture')
                    ->required()
                    ->dehydrated()
                    ->unique('depot_invoices', 'number'),
                DatePicker::make('date')
                    ->label('Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required()
                    ->default(now()),
                Select::make('client_id')
                    ->label('Client')
                    ->options(Client::pluck('nom', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('depot_id')
                    ->label('Dépôt')
                    ->options(Depot::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('items', [])),
                TextInput::make('issuer_name')
                    ->label('Émetteur')
                    ->default('CORRIDOR PETROLEUM')
                    ->readOnly()
                    ->required(),

                Repeater::make('items')
                    ->label('Détails de la facturation')
                    ->addActionLabel('Ajouter un compartiment')
                    ->schema([
                        Select::make('compartment_id')
                            ->label('Compartiment')
                            ->options(function (Get $get) {
                                $depotId = $get('../../depot_id');
                                if (!$depotId) return [];

                                return Compartment::where('depot_id', $depotId)
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [$c->id => "{$c->product} - {$c->quantity} L"]);
                            })
                            ->required()
                            ->live()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        TextInput::make('quantity')
                            ->label('Quantité')
                            ->numeric()
                            ->required()
                            ->live(debounce: 2000)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $comp = Compartment::find($get('compartment_id'));
                                if ($comp && $state > $comp->quantity) {
                                    $set('quantity', $comp->quantity);
                                    Notification::make()
                                        ->title('Quantité insuffisante')
                                        ->body("La quantité disponible est de {$comp->quantity} L")
                                        ->warning()
                                        ->send();
                                }
                                $this->updateItemTotal($get, $set);
                            }),
                        TextInput::make('unit_price')
                            ->label('Prix Unitaire')
                            ->numeric()
                            ->required()
                            ->live(debounce: 2000)
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateItemTotal($get, $set)),
                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->readOnly()
                            ->required(),
                    ])
                    ->afterStateUpdated(fn (Set $set) => $this->updateInvoiceTotal($set))
                    ->columns(4)
                    ->minItems(1),

                TextInput::make('total_amount')
                    ->label('Montant Total')
                    ->readOnly()
                    ->numeric()
                    ->prefix('FCFA'),
            ]);
    }

    public function updateItemTotal(Get $get, Set $set)
    {
        $qty = floatval($get('quantity') ?: 0);
        $price = floatval($get('unit_price') ?: 0);
        $set('total', $qty * $price);
        $this->updateInvoiceTotal($set);
    }

    public function updateInvoiceTotal(Set $set)
    {
        $items = $this->items ?: [];
        $total = collect($items)->sum('total');
        $this->total_amount = $total;
        $set('total_amount', $total);
    }

    public function create()
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            $invoice = DepotInvoice::create([
                'number' => $data['number'],
                'date' => $data['date'],
                'client_id' => $data['client_id'],
                'depot_id' => $data['depot_id'],
                'issuer_name' => $data['issuer_name'],
                'total_amount' => $data['total_amount'],
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'compartment_id' => $item['compartment_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                ]);

                // Mise à jour du stock
                $compartment = Compartment::find($item['compartment_id']);
                $compartment->decrement('quantity', $item['quantity']);
            }
        });

        $this->closeModal();
        $this->dispatch('depot-invoice-updated');

        Notification::make()
            ->title('Facture dépôt enregistrée')
            ->success()
            ->send();
    }

    public static function modalMaxWidth(): string
    {
        return '7xl';
    }

    public function render()
    {
        return view('livewire.modals.depot-invoice.add-depot-invoice');
    }
}
