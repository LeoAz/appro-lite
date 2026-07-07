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
                    ->readOnly()
                    ->dehydrated()
                    ->unique('invoices', 'number'),
                DatePicker::make('date')
                    ->label('Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required()
                    ->default(now()),
                Select::make('client_name')
                    ->label('Client')
                    ->options(function () {
                        return Load::where('status', LoadStatus::Unloaded)
                            ->whereNotNull('client_name')
                            ->whereNotExists(function ($q) {
                                $q->select(DB::raw(1))
                                    ->from('invoice_items')
                                    ->whereColumn('invoice_items.load_id', 'loads.id');
                            })
                            ->distinct()
                            ->pluck('client_name', 'client_name');
                    })
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
                    ->label('Livraisons')
                    ->addActionLabel('Ajouter des livraisons')
                    ->schema([
                        Select::make('load_id')
                            ->label('Livraison')
                            ->searchable()
                            ->options(function (Get $get) {
                                $clientName = $get('../../client_name');
                                if (!$clientName) return [];
                                return Load::where('client_name', $clientName)
                                    ->where('status', LoadStatus::Unloaded)
                                    ->whereNotExists(function ($query) {
                                        $query->select(DB::raw(1))
                                            ->from('invoice_items')
                                            ->whereColumn('invoice_items.load_id', 'loads.id');
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
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateItemTotals($get, $set)),
                        TextInput::make('unit_price')
                            ->label('Prix unitaire')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->updateItemTotals($get, $set)),
                        TextInput::make('missing_quantity')
                            ->label('Manquant')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated(),
                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->readOnly()
                            ->dehydrated(),
                    ])
                    ->columns(6)
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
                        \Filament\Forms\Components\Placeholder::make('total_missing_placeholder')
                            ->label('Total Manquant')
                            ->content(fn (Get $get) => number_format($get('total_missing') ?: 0, 0, '.', ' ') . ' L')
                            ->extraAttributes(['class' => 'text-right font-bold text-xl']),
                        \Filament\Forms\Components\Placeholder::make('total_amount_placeholder')
                            ->label('Montant Total')
                            ->content(fn (Get $get) => number_format($get('total_amount') ?: 0, 0, '.', ' ') . ' FCFA')
                            ->extraAttributes(['class' => 'text-right font-bold text-xl']),
                    ])
                    ->columns(2)
                    ->compact(),

                \Filament\Forms\Components\Hidden::make('total_missing')
                    ->default(0),
                \Filament\Forms\Components\Hidden::make('total_amount')
                    ->default(0),
            ]);
    }

    public function updateItemTotals($get, Set $set, $qtyDelivered = null, $unitPrice = null)
    {
        if ($get instanceof Get) {
            $loadId = $get('load_id');
            $qtyDelivered = floatval($get('quantity_delivered') ?: 0);
            $unitPrice = floatval($get('unit_price') ?: 0);
        } else {
            $loadId = $get;
            $qtyDelivered = floatval($qtyDelivered ?: 0);
            $unitPrice = floatval($unitPrice ?: 0);
        }

        $load = Load::find($loadId);
        $missing = 0;
        if ($load) {
            $missing = floatval($load->volume) - $qtyDelivered;
        }

        $set('missing_quantity', $missing);
        $set('total', $qtyDelivered * $unitPrice);

        // Update invoice totals whenever an item is updated
        $this->updateInvoiceTotals($set);
    }

    public function updateInvoiceTotals(Set $set = null)
    {
        $items = $this->items ?: [];
        $totalMissing = 0;
        $totalAmount = 0;

        foreach ($items as $item) {
            $totalMissing += floatval($item['missing_quantity'] ?? 0);
            $totalAmount += floatval($item['total'] ?? 0);
        }

        $this->total_missing = $totalMissing;
        $this->total_amount = $totalAmount;

        if ($set instanceof Set) {
            try {
                $set('total_missing', $totalMissing);
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
                'client_name' => $data['client_name'],
                'issuer_name' => $data['issuer_name'],
                'total_missing' => $data['total_missing'],
                'total_amount' => $data['total_amount'],
            ]);

            foreach ($data['items'] as $item) {
                $invoice->items()->create([
                    'load_id' => $item['load_id'],
                    'bl_number' => $item['bl_number'] ?? null,
                    'quantity_delivered' => $item['quantity_delivered'],
                    'unit_price' => $item['unit_price'],
                    'missing_quantity' => $item['missing_quantity'],
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
