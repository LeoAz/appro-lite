<?php

namespace App\Livewire\Modals\Invoice;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Load;
use App\Enums\LoadStatus;
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

class AddInvoice extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public $number;
    public $date;
    public $client_id;
    public $client_name;
    public $issuer_name = 'CORRIDOR PETROLEUM';
    public $items = [];
    public $total_missing = 0;
    public $total_amount = 0;

    public function mount(): void
    {
        $lastInvoice = Invoice::whereYear('date', date('Y'))
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice && preg_match('/FAC-\d{4}-(\d{5})/', $lastInvoice->number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }

        $number = 'FAC-' . date('Y') . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        $this->form->fill([
            'date' => now(),
            'number' => $number,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('number')
                    ->label('Numéro de facture')
                    ->required()
                    ->dehydrated()
                    ->unique('invoices', 'number'),
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
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('items', []);
                        $client = Client::find($state);
                        if ($client) {
                            $set('client_name', $client->nom);
                        }
                    }),
                \Filament\Forms\Components\Hidden::make('client_name'),
                TextInput::make('issuer_name')
                    ->label('Émetteur')
                    ->default('CORRIDOR PETROLEUM')
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

                                // Récupérer les IDs déjà sélectionnés dans le repeater pour les exclure
                                $selectedIds = collect($get('../../items'))
                                    ->pluck('load_id')
                                    ->filter()
                                    ->toArray();

                                return Load::where('client_id', $clientId)
                                    ->where('status', LoadStatus::Unloaded)
                                    ->whereNotExists(function ($query) {
                                        $query->select(DB::raw(1))
                                            ->from('invoice_items')
                                            ->whereColumn('invoice_items.load_id', 'loads.id');
                                    })
                                    ->when($selectedIds, function ($query) use ($selectedIds) {
                                        $query->whereNotIn('id', $selectedIds);
                                    })
                                    ->get()
                                    ->mapWithKeys(fn ($load) => [$load->id => "Produit: " . ($load->product ?? 'N/A') . " - Camion: " . ($load->vehicle->registration ?? $load->vehicle_registration ?? 'N/A') . " - Date: {$load->unload_date->format('d/m/Y')} - Vol: {$load->volume}L"]);
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $load = Load::find($state);
                                if ($load) {
                                    $set('quantity_delivered', $load->volume);
                                    $this->updateItemTotals($state, $set, $load->volume, 0); // Trigger initial calculation
                                }
                            }),
                        TextInput::make('bl_number')
                            ->label('N° BL')
                            ->placeholder('Facultatif'),
                        TextInput::make('quantity_delivered')
                            ->label('Quantité livrée')
                            ->numeric()
                            ->required()
                            ->live(debounce: 2000)
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateItemTotals($get, $set)),
                        TextInput::make('unit_price')
                            ->label('Prix unitaire')
                            ->numeric()
                            ->required()
                            ->live(debounce: 2000)
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

                \Filament\Forms\Components\Hidden::make('total_amount')
                    ->default(0),
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

    public function create()
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            $invoice = Invoice::create([
                'number' => $data['number'],
                'date' => $data['date'],
                'client_id' => $data['client_id'],
                'client_name' => $data['client_name'],
                'issuer_name' => $data['issuer_name'],
                'total_missing' => 0,
                'total_amount' => $data['total_amount'],
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'load_id' => $item['load_id'],
                    'bl_number' => $item['bl_number'] ?? null,
                    'quantity_delivered' => $item['quantity_delivered'],
                    'unit_price' => $item['unit_price'],
                    'missing_quantity' => 0,
                    'total' => $item['total'],
                ]);

                // Mettre à jour le statut de la livraison
                Load::where('id', $item['load_id'])->update(['status' => LoadStatus::Invoiced]);
            }
        });

        Notification::make()
            ->title('Facture créée')
            ->success()
            ->send();

        $this->dispatch('invoice-updated');
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return '7xl';
    }

    public function render()
    {
        return view('livewire.modals.invoice.add-invoice');
    }
}
