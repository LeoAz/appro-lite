<?php

namespace App\Livewire\Modals\Client;

use App\Models\Client;
use App\Models\ClientPayment;
use App\Models\DepotInvoiceItem;
use App\Models\InvoiceItem;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use LivewireUI\Modal\ModalComponent;

class EditClientPayment extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public ClientPayment $payment;
    public ?array $data = [];

    public function mount(ClientPayment $payment): void
    {
        $this->payment = $payment;
        $data = $payment->toArray();

        // Remplir les items liés si c'est un règlement sur chargement
        if ($payment->payment_type === 'load') {
            $data['selected_items'] = $payment->invoiceItems->map(function ($item) {
                return [
                    'invoice_item_id' => $item->id,
                    'missing_quantity' => $item->missing_quantity,
                    'quantity_delivered' => $item->quantity_delivered,
                    'unit_price' => $item->unit_price,
                    'new_total' => $item->total,
                    'original_total' => $item->total + ($item->missing_quantity * $item->unit_price),
                ];
            })->toArray();
        }

        // Remplir les items liés si c'est un règlement sur dépôt
        if ($payment->payment_type === 'depot') {
            $data['depot_items'] = $payment->depotInvoiceItems->map(function ($item) {
                return [
                    'depot_invoice_item_id' => $item->id,
                    'total' => $item->total,
                ];
            })->toArray();
        }

        $this->form->fill($data);
    }

    public static function modalMaxWidth(): string
    {
        return '7xl';
    }

    public function form(Form $form): Form
    {
        $paymentType = $this->payment->is_advance ? 'advance' : ($this->payment->parent_id ? 'payment_via_advance' : $this->payment->payment_type);

        return $form
            ->schema([
                Wizard::make([
                    Step::make('Informations')
                        ->schema([
                            Select::make('payment_type')
                                ->label('Type de opération')
                                ->options([
                                    'load' => 'Règlement sur chargement',
                                    'depot' => 'Règlement sur dépôt',
                                    'advance' => 'Avance client',
                                    'payment_via_advance' => 'Règlement via avance',
                                ])
                                ->formatStateUsing(fn() => $paymentType)
                                ->disabled()
                                ->dehydrated(false),
                            Select::make('client_id')
                                ->label('Client concerné')
                                ->options(Client::all()->pluck('nom', 'id'))
                                ->searchable()
                                ->required()
                                ->disabled()
                                ->placeholder('Choisir un client'),
                            TextInput::make('amount')
                                ->label(fn() => $this->payment->is_advance ? 'Montant de l\'avance' : 'Montant du règlement')
                                ->numeric()
                                ->required()
                                ->prefix('FCFA')
                                ->live(debounce: 500),
                            DatePicker::make('date')
                                ->label(fn() => $this->payment->is_advance ? 'Date de l\'avance' : 'Date du règlement')
                                ->required(),
                            Select::make('payment_method')
                                ->label('Méthode de paiement')
                                ->options(\App\Models\PaymentMethod::pluck('name', 'name'))
                                ->required(),
                            TextInput::make('reference')
                                ->label('Référence / N° de transaction')
                                ->placeholder('Ex: CHQ 123456'),
                            Textarea::make('note')
                                ->label('Notes / Observations')
                                ->columnSpanFull(),

                            Placeholder::make('parent_info')
                                ->label('Avance utilisée')
                                ->visible(fn() => (bool) $this->payment->parent_id)
                                ->content(fn() => "Utilise l'avance du " . $this->payment->parent?->date->format('d/m/Y') . " (#" . ($this->payment->parent?->reference ?? $this->payment->parent?->id) . ")")
                                ->columnSpanFull(),
                        ])->columns(2),

                    Step::make('Véhicules concernés')
                        ->hidden(fn () => $paymentType !== 'load')
                        ->schema([
                            Repeater::make('selected_items')
                                ->label('Chargements liés')
                                ->schema([
                                    Select::make('invoice_item_id')
                                        ->label('Chargement')
                                        ->options(function (Get $get) {
                                            $clientId = $this->payment->client_id;
                                            if (!$clientId) return [];
                                            return InvoiceItem::where(function($query) use ($clientId) {
                                                $query->whereHas('invoice', fn($q) => $q->where('client_id', $clientId))
                                                      ->orWhereHas('delivery', fn($q) => $q->where('client_id', $clientId));
                                            })
                                                ->where(function($query) {
                                                    $query->where(function($q) {
                                                        $q->where('is_paid', false)
                                                          ->whereNull('client_payment_id');
                                                    })->orWhere('client_payment_id', $this->payment->id);
                                                })
                                                ->with(['delivery', 'invoice'])
                                                ->get()
                                                ->mapWithKeys(fn($item) => [
                                                    $item->id => "Facture: " . ($item->invoice->number ?? 'N/A') . " - Véhicule: " . ($item->delivery->vehicle_registration ?? 'N/A') . " - Date: " . ($item->delivery->unload_date?->format('d/m/Y') ?? 'N/A') . " - Reste: " . number_format($item->total, 0, '.', ' ') . " FCFA"
                                                ]);
                                        })
                                        ->searchable()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            $item = InvoiceItem::find($state);
                                            if ($item) {
                                                $set('original_total', $item->total);
                                                $set('unit_price', $item->unit_price);
                                                $set('quantity_delivered', $item->quantity_delivered);
                                                $set('missing_quantity', $item->missing_quantity ?? 0);
                                                $set('new_total', $item->total);
                                            }
                                        }),
                                    TextInput::make('missing_quantity')
                                        ->label('Manquant (Litres)')
                                        ->numeric()
                                        ->default(0)
                                        ->required()
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                            $qty = floatval($get('quantity_delivered') ?: 0);
                                            $price = floatval($get('unit_price') ?: 0);
                                            $missing = floatval($state ?: 0);
                                            $set('new_total', ($qty - $missing) * $price);
                                        }),
                                    TextInput::make('quantity_delivered')
                                        ->label('Qté Livrée')
                                        ->numeric()
                                        ->readOnly(),
                                    TextInput::make('unit_price')
                                        ->label('Prix U')
                                        ->numeric()
                                        ->readOnly(),
                                    TextInput::make('new_total')
                                        ->label('Nouveau Total')
                                        ->numeric()
                                        ->readOnly()
                                        ->prefix('FCFA'),
                                    \Filament\Forms\Components\Hidden::make('original_total'),
                                ])
                                ->columns(5)
                                ->addActionLabel('Ajouter un chargement')
                                ->itemLabel(function (array $state) {
                                    if (empty($state['invoice_item_id'])) return null;
                                    $item = InvoiceItem::with(['delivery', 'invoice'])->find($state['invoice_item_id']);
                                    return $item ? "Facture: " . ($item->invoice->number ?? 'N/A') . " - " . ($item->delivery->vehicle_registration ?? 'N/A') : null;
                                }),
                        ]),

                    Step::make('Factures (Dépôt)')
                        ->hidden(fn () => $paymentType !== 'depot')
                        ->schema([
                            Repeater::make('depot_items')
                                ->label('Factures de dépôt liées')
                                ->schema([
                                    Select::make('depot_invoice_item_id')
                                        ->label('Facture Dépôt')
                                        ->options(function (Get $get) {
                                            $clientId = $this->payment->client_id;
                                            if (!$clientId) return [];
                                            return DepotInvoiceItem::whereHas('depotInvoice', fn($q) => $q->where('client_id', $clientId))
                                                ->where(function($query) {
                                                    $query->where(function($q) {
                                                        $q->where('is_paid', false)
                                                          ->whereNull('client_payment_id');
                                                    })->orWhere('client_payment_id', $this->payment->id);
                                                })
                                                ->with(['depotInvoice', 'compartment'])
                                                ->get()
                                                ->mapWithKeys(fn($item) => [
                                                    $item->id => "Facture: " . ($item->depotInvoice->number ?? 'N/A') . " - Produit: " . ($item->compartment->product ?? 'N/A') . " - Date: " . ($item->depotInvoice->date?->format('d/m/Y') ?? 'N/A') . " - Total: " . number_format($item->total, 0, '.', ' ') . " FCFA"
                                                ]);
                                        })
                                        ->searchable()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            $item = DepotInvoiceItem::find($state);
                                            if ($item) {
                                                $set('total', $item->total);
                                            }
                                        }),
                                    TextInput::make('total')
                                        ->label('Montant')
                                        ->numeric()
                                        ->readOnly()
                                        ->prefix('FCFA'),
                                ])
                                ->columns(2)
                                ->addActionLabel('Ajouter une facture dépôt')
                                ->itemLabel(function (array $state) {
                                    if (empty($state['depot_invoice_item_id'])) return null;
                                    $item = DepotInvoiceItem::with(['depotInvoice', 'compartment'])->find($state['depot_invoice_item_id']);
                                    return $item ? "Facture: " . ($item->depotInvoice->number ?? 'N/A') . " - " . ($item->compartment->product ?? 'N/A') : null;
                                }),
                        ]),

                    Step::make('Récapitulatif')
                        ->schema([
                            Placeholder::make('recap_amount')
                                ->label('Montant du règlement')
                                ->content(fn (Get $get) => number_format($get('amount') ?: 0, 0, '.', ' ') . ' FCFA'),
                            Placeholder::make('recap_items')
                                ->label('Éléments concernés')
                                ->content(function (Get $get) use ($paymentType) {
                                    if ($paymentType === 'load') {
                                        $items = $get('selected_items') ?: [];
                                        if (empty($items)) return 'Aucun chargement sélectionné';
                                        $summary = [];
                                        foreach ($items as $i) {
                                            if (empty($i['invoice_item_id'])) continue;
                                            $item = InvoiceItem::with(['delivery', 'invoice'])->find($i['invoice_item_id']);
                                            $invoiceNumber = $item->invoice->number ?? 'N/A';
                                            $vehicle = $item->delivery->vehicle_registration ?? 'N/A';
                                            $summary[] = "- Facture {$invoiceNumber} ({$vehicle}) : Nouveau total " . number_format($i['new_total'], 0, '.', ' ') . " FCFA (Manquant: {$i['missing_quantity']}L)";
                                        }
                                        return new \Illuminate\Support\HtmlString(implode('<br>', $summary));
                                    } elseif ($paymentType === 'depot') {
                                        $items = $get('depot_items') ?: [];
                                        if (empty($items)) return 'Aucune facture dépôt sélectionnée';
                                        $summary = [];
                                        foreach ($items as $i) {
                                            if (empty($i['depot_invoice_item_id'])) continue;
                                            $item = DepotInvoiceItem::with(['depotInvoice', 'compartment'])->find($i['depot_invoice_item_id']);
                                            $invoiceNumber = $item->depotInvoice->number ?? 'N/A';
                                            $product = $item->compartment->product ?? 'N/A';
                                            $summary[] = "- Facture {$invoiceNumber} ({$product}) : Montant " . number_format($item->total, 0, '.', ' ') . " FCFA";
                                        }
                                        return new \Illuminate\Support\HtmlString(implode('<br>', $summary));
                                    }
                                    return 'N/A';
                                })
                                ->hidden(fn() => $paymentType === 'advance'),
                        ]),
                ])->contained(false)
            ])
            ->statePath('data');
    }

    public function save()
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            // Mettre à jour le paiement
            $this->payment->update([
                'amount' => $data['amount'],
                'date' => $data['date'],
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            // 1. Gérer les chargements (load)
            if ($this->payment->payment_type === 'load') {
                // Détacher les anciens items non présents dans la nouvelle sélection
                $newSelectedIds = collect($data['selected_items'] ?? [])->pluck('invoice_item_id')->filter()->toArray();

                $oldItems = $this->payment->invoiceItems;
                foreach ($oldItems as $oldItem) {
                    if (!in_array($oldItem->id, $newSelectedIds)) {
                        $oldItem->update([
                            'client_payment_id' => null,
                            'is_paid' => false,
                            'missing_quantity' => 0,
                            'total' => $oldItem->quantity_delivered * $oldItem->unit_price,
                        ]);

                        // Détacher aussi le règlement du chargement
                        if ($oldItem->delivery) {
                            $oldItem->delivery->update([
                                'client_payment_id' => null,
                                'status' => \App\Enums\LoadStatus::Unloaded, // Revenir à l'état livré
                            ]);
                        }

                        // Mettre à jour la facture parente
                        $invoice = $oldItem->invoice;
                        if ($invoice) {
                            $invoice->update([
                                'total_missing' => $invoice->items()->sum('missing_quantity'),
                                'total_amount' => $invoice->items()->sum('total'),
                            ]);
                        }
                    }
                }

                // Mettre à jour ou attacher les nouveaux items
                if (!empty($data['selected_items'])) {
                    foreach ($data['selected_items'] as $itemData) {
                        if (empty($itemData['invoice_item_id'])) continue;

                        $item = InvoiceItem::find($itemData['invoice_item_id']);
                        if ($item) {
                            $item->update([
                                'client_payment_id' => $this->payment->id,
                                'missing_quantity' => $itemData['missing_quantity'],
                                'total' => $itemData['new_total'],
                                'is_paid' => true,
                            ]);

                            // Lier aussi le règlement au chargement
                            if ($item->delivery) {
                                $item->delivery->update([
                                    'client_payment_id' => $this->payment->id,
                                    'status' => \App\Enums\LoadStatus::Invoiced,
                                ]);
                            }

                            // Mettre à jour la facture parente
                            $invoice = $item->invoice;
                            if ($invoice) {
                                $invoice->update([
                                    'total_missing' => $invoice->items()->sum('missing_quantity'),
                                    'total_amount' => $invoice->items()->sum('total'),
                                ]);
                            }
                        }
                    }
                }
            }

            // 2. Gérer les dépôts (depot)
            if ($this->payment->payment_type === 'depot') {
                $newDepotIds = collect($data['depot_items'] ?? [])->pluck('depot_invoice_item_id')->filter()->toArray();

                $oldDepotItems = $this->payment->depotInvoiceItems;
                foreach ($oldDepotItems as $oldItem) {
                    if (!in_array($oldItem->id, $newDepotIds)) {
                        $oldItem->update([
                            'client_payment_id' => null,
                            'is_paid' => false,
                        ]);
                    }
                }

                if (!empty($data['depot_items'])) {
                    foreach ($data['depot_items'] as $itemData) {
                        if (empty($itemData['depot_invoice_item_id'])) continue;

                        $item = DepotInvoiceItem::find($itemData['depot_invoice_item_id']);
                        if ($item) {
                            $item->update([
                                'client_payment_id' => $this->payment->id,
                                'is_paid' => true,
                            ]);
                        }
                    }
                }
            }
        });

        Notification::make()
            ->title('Règlement mis à jour')
            ->success()
            ->send();

        $this->dispatch('update-client');
        $this->closeModal();
    }

    public function delete()
    {
        DB::transaction(function () {
            // Si c'est un règlement sur chargement, remettre à zéro les items liés
            if ($this->payment->payment_type === 'load') {
                $items = $this->payment->invoiceItems;
                foreach ($items as $item) {
                    $item->update([
                        'client_payment_id' => null,
                        'is_paid' => false,
                        'missing_quantity' => 0,
                        'total' => $item->quantity_delivered * $item->unit_price,
                    ]);

                    // Détacher aussi le règlement du chargement
                    if ($item->delivery) {
                        $item->delivery->update([
                            'client_payment_id' => null,
                            'status' => \App\Enums\LoadStatus::Unloaded,
                        ]);
                    }

                    // Mettre à jour la facture parente
                    $invoice = $item->invoice;
                    $invoice->update([
                        'total_missing' => $invoice->items()->sum('missing_quantity'),
                        'total_amount' => $invoice->items()->sum('total'),
                    ]);
                }
            }

            $this->payment->delete();
        });

        Notification::make()
            ->title('Règlement supprimé')
            ->warning()
            ->send();

        $this->dispatch('update-client');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.modals.client.edit-client-payment');
    }

    public function convertToPayment(string $type)
    {
        if (!$this->payment->is_advance) {
            return;
        }

        $this->payment->update([
            'is_advance' => false,
            'payment_type' => $type,
        ]);

        Notification::make()
            ->title('Avance convertie en règlement')
            ->success()
            ->send();

        if ($type === 'load') {
            // Si conversion en chargement, on pourrait rediriger vers un formulaire de sélection des chargements
            // Mais pour simplifier ici, on change juste le type.
            // L'utilisateur pourra ensuite l'éditer si nécessaire ou on laisse ainsi.
        }

        $this->dispatch('update-client');
        $this->closeModal();
    }
}
