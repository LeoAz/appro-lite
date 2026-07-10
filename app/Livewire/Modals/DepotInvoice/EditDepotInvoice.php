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

class EditDepotInvoice extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public DepotInvoice $invoice;
    public $number;
    public $date;
    public $client_id;
    public $depot_id;
    public $product;
    public $issuer_name;
    public $items = [];
    public $total_amount = 0;

    public function mount(DepotInvoice $invoice): void
    {
        $this->invoice = $invoice;
        $this->form->fill([
            'number' => $invoice->number,
            'date' => $invoice->date,
            'client_id' => $invoice->client_id,
            'depot_id' => $invoice->depot_id,
            'product' => $invoice->product,
            'issuer_name' => $invoice->issuer_name,
            'items' => $invoice->items->map(fn ($item) => [
                'id' => $item->id,
                'compartment_id' => $item->compartment_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ])->toArray(),
            'total_amount' => $invoice->total_amount,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->model($this->invoice)
            ->schema([
                TextInput::make('number')
                    ->label('Numéro de facture')
                    ->required()
                    ->dehydrated()
                    ->unique('depot_invoices', 'number', ignoreRecord: true),
                DatePicker::make('date')
                    ->label('Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required(),
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
                                // Attention ici, pour le stock, on doit prendre en compte l'ancienne quantité si c'est une modification
                                $itemId = $get('id');
                                $oldQty = 0;
                                if ($itemId) {
                                    $oldQty = DepotInvoiceItem::find($itemId)?->quantity ?: 0;
                                }

                                $comp = Compartment::find($get('compartment_id'));
                                if ($comp && $state > ($comp->quantity + $oldQty)) {
                                    $set('quantity', $comp->quantity + $oldQty);
                                    Notification::make()
                                        ->title('Quantité insuffisante')
                                        ->body("La quantité disponible est de " . ($comp->quantity + $oldQty) . " L")
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
                        \Filament\Forms\Components\Hidden::make('id'),
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

    public function update()
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            // Remettre en stock les anciens items
            foreach ($this->invoice->items as $oldItem) {
                $oldItem->compartment->increment('quantity', $oldItem->quantity);
            }

            // Supprimer les anciens items
            $this->invoice->items()->delete();

            // Mettre à jour la facture
            $this->invoice->update([
                'number' => $data['number'],
                'date' => $data['date'],
                'client_id' => $data['client_id'],
                'depot_id' => $data['depot_id'],
                'issuer_name' => $data['issuer_name'],
                'total_amount' => $data['total_amount'],
            ]);

            // Créer les nouveaux items et déduire du stock
            foreach ($data['items'] as $item) {
                $this->invoice->items()->create([
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
        $this->dispatch('update-client');

        Notification::make()
            ->title('Facture dépôt mise à jour')
            ->success()
            ->send();
    }

    public static function modalMaxWidth(): string
    {
        return '7xl';
    }

    public function render()
    {
        return view('livewire.modals.depot-invoice.edit-depot-invoice');
    }
}
