<?php

namespace App\Livewire\Modals\Invoice;

use App\Enums\LoadStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Load;
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

class EditInvoice extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Invoice $invoice;
    public $number;
    public $date;
    public $client_id;
    public $client_name;
    public $issuer_name;
    public $items = [];
    public $total_missing = 0;
    public $total_amount = 0;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
        $this->form->fill([
            'number' => $invoice->number,
            'date' => $invoice->date,
            'client_id' => $invoice->client_id,
            'client_name' => $invoice->client_name,
            'issuer_name' => $invoice->issuer_name,
            'items' => $invoice->items->map(fn ($item) => [
                'load_id' => $item->load_id,
                'bl_number' => $item->bl_number,
                'quantity_delivered' => $item->quantity_delivered,
                'unit_price' => $item->unit_price,
                'missing_quantity' => $item->missing_quantity,
                'total' => $item->total,
            ])->toArray(),
            'total_missing' => $invoice->total_missing,
            'total_amount' => $invoice->total_amount,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('number')
                    ->label('Numéro de facture')
                    ->required()
                    ->unique('invoices', 'number', ignorable: $this->invoice),
                DatePicker::make('date')
                    ->label('Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required(),
                Select::make('client_id')
                    ->label('Client')
                    ->options(Client::pluck('nom', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $client = Client::find($state);
                        if ($client) {
                            $set('client_name', $client->nom);
                        }
                        $set('items', []);
                    }),
                \Filament\Forms\Components\Hidden::make('client_name'),
                TextInput::make('issuer_name')
                    ->label('Émetteur')
                    ->readOnly()
                    ->required(),

                Repeater::make('items')
                    ->label('Livraisons')
                    ->addActionLabel('Ajouter des livraisons')
                    ->schema([
                        Select::make('load_id')
                            ->label('Livraison')
                            ->searchable()
                            ->options(function (Get $get) {
                                $clientId = $get('../../client_id');
                                if (!$clientId) return [];

                                // Récupérer les IDs déjà sélectionnés dans le repeater
                                $selectedIds = collect($get('../../items'))
                                    ->pluck('load_id')
                                    ->filter()
                                    ->toArray();

                                // Récupérer les IDs initialement dans la facture (pour permettre de les garder)
                                $initialIds = $this->invoice->items->pluck('load_id')->toArray();

                                return Load::where('client_id', $clientId)
                                    ->where(function ($query) use ($selectedIds, $initialIds) {
                                        $query->where('status', LoadStatus::Unloaded)
                                            ->whereNotExists(function ($q) {
                                                $q->select(DB::raw(1))
                                                    ->from('invoice_items')
                                                    ->whereColumn('invoice_items.load_id', 'loads.id');
                                            })
                                            ->orWhereIn('id', $initialIds);
                                    })
                                    ->get()
                                    ->mapWithKeys(fn ($load) => [$load->id => "Produit: " . ($load->product ?? 'N/A') . " - Camion: " . ($load->vehicle->registration ?? $load->vehicle_registration ?? 'N/A') . " - Date: {$load->unload_date?->format('d/m/Y')} - Vol: {$load->volume}L"]);
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $load = Load::find($state);
                                if ($load) {
                                    $set('quantity_delivered', $load->volume);
                                    $this->updateItemTotals($state, $set, $load->volume, 0);
                                }
                            }),
                        TextInput::make('bl_number')
                            ->label('N° BL')
                            ->placeholder('Facultatif'),
                        TextInput::make('quantity_delivered')
                            ->label('Quantité livrée')
                            ->numeric()
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateItemTotals($get, $set)),
                        TextInput::make('unit_price')
                            ->label('Prix unitaire')
                            ->numeric()
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateItemTotals($get, $set)),
                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated(),
                    ])
                    ->columns(5)
                    ->reorderable(false)
                    ->itemLabel(function (array $state) {
                        if (!isset($state['load_id'])) return null;
                        $load = Load::find($state['load_id']);
                        $vehicle = $load->vehicle->registration ?? $load->vehicle_registration ?? 'N/A';
                        $product = $load->product ?? 'N/A';
                        return "Produit: {$product} - Véhicule: {$vehicle}";
                    })
                    ->live()
                    ->afterStateHydrated(function (Set $set) {
                        $this->updateInvoiceTotals($set);
                    })
                    ->afterStateUpdated(function (Set $set) {
                        $this->updateInvoiceTotals($set);
                    }),

                \Filament\Forms\Components\Section::make()
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('total_amount_placeholder')
                            ->label('Montant Total')
                            ->content(fn (Get $get) => number_format($get('total_amount') ?: 0, 0, '.', ' ') . ' FCFA')
                            ->extraAttributes(['class' => 'text-right font-bold text-xl']),
                    ])
                    ->columns(1)
                    ->compact(),

                \Filament\Forms\Components\Hidden::make('total_amount'),
            ]);
    }

    public function updateItemTotals($get, Set $set, $qtyDelivered = null, $unitPrice = null)
    {
        if ($get instanceof Get) {
            $qtyDelivered = floatval($get('quantity_delivered') ?: 0);
            $unitPrice = floatval($get('unit_price') ?: 0);
        } else {
            $qtyDelivered = floatval($qtyDelivered ?: 0);
            $unitPrice = floatval($unitPrice ?: 0);
        }

        $set('total', $qtyDelivered * $unitPrice);

        // Update invoice totals whenever an item is updated
        $this->updateInvoiceTotals($set);
    }

    public function updateInvoiceTotals(Set $set = null)
    {
        $items = $this->items ?: [];
        $totalAmount = 0;

        foreach ($items as $item) {
            $totalAmount += floatval($item['total'] ?? 0);
        }

        $this->total_amount = $totalAmount;

        if ($set instanceof Set) {
            try {
                $set('total_amount', $totalAmount);
            } catch (\Error $e) {
                // Ignore container initialization error in tests
            }
        }
    }

    public function update()
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            $this->invoice->update([
                'number' => $data['number'],
                'date' => $data['date'],
                'client_id' => $data['client_id'],
                'client_name' => $data['client_name'],
                'total_missing' => 0,
                'total_amount' => $data['total_amount'],
            ]);

            // Restaurer le statut des livraisons actuelles de la facture avant de les mettre à jour
            $currentLoadIds = $this->invoice->items->pluck('load_id')->toArray();
            Load::whereIn('id', $currentLoadIds)->update(['status' => LoadStatus::Unloaded]);

            // Supprimer les items actuels
            $this->invoice->items()->delete();

            // Créer les nouveaux items et mettre à jour le statut des livraisons
            foreach ($data['items'] as $itemData) {
                $this->invoice->items()->create([
                    'load_id' => $itemData['load_id'],
                    'bl_number' => $itemData['bl_number'] ?? null,
                    'quantity_delivered' => $itemData['quantity_delivered'],
                    'unit_price' => $itemData['unit_price'],
                    'missing_quantity' => 0,
                    'total' => $itemData['total'],
                ]);

                Load::where('id', $itemData['load_id'])->update(['status' => LoadStatus::Invoiced]);
            }
        });

        Notification::make()
            ->title('Facture mise à jour')
            ->success()
            ->send();

        $this->dispatch('invoice-updated');
        $this->dispatch('update-client');
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return '7xl';
    }

    public function render()
    {
        return view('livewire.modals.invoice.edit-invoice');
    }
}
