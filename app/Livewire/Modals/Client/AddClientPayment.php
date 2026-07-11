<?php

namespace App\Livewire\Modals\Client;

use App\Models\Client;
use App\Models\ClientPayment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use LivewireUI\Modal\ModalComponent;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Actions\Action;
use App\Models\InvoiceItem;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\DB;

class AddClientPayment extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public ?Client $client = null;
    public ?string $type = 'load'; // 'load', 'depot', or 'advance'
    public ?array $data = [];

    public function mount(?Client $client = null, string $type = 'load'): void
    {
        $this->type = $type;
        if ($client) {
            $this->client = $client;
            $this->form->fill([
                'client_id' => $client->id,
                'date' => now(),
            ]);
        } else {
            $this->form->fill([
                'date' => now(),
            ]);
        }
    }

    public static function modalMaxWidth(): string
    {
        return '7xl';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Informations')
                        ->schema([
                            Select::make('client_id')
                                ->label('Client concerné')
                                ->options(Client::all()->pluck('nom', 'id'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set) {
                                    $set('items', []);
                                    $set('parent_id', null);
                                })
                                ->placeholder('Choisir un client'),
                            Select::make('parent_id')
                                ->label('Utiliser une avance existante')
                                ->placeholder('Sélectionner une avance')
                                ->options(function (Get $get) {
                                    $clientId = $get('client_id');
                                    if (!$clientId) return [];
                                    return ClientPayment::where('client_id', $clientId)
                                        ->where('is_advance', true)
                                        ->where(function($q) {
                                            $q->whereNotExists(function($query) {
                                                $query->select(DB::raw(1))
                                                    ->from('client_payments as children')
                                                    ->whereColumn('children.parent_id', 'client_payments.id');
                                            })->orWhereRaw('(select sum(amount) from client_payments as children where children.parent_id = client_payments.id) < client_payments.amount');
                                        })
                                        ->get()
                                        ->mapWithKeys(function ($payment) {
                                            $used = $payment->children()->sum('amount');
                                            $remaining = $payment->amount - $used;
                                            return [$payment->id => "Avance du {$payment->date->format('d/m/Y')} - Restant: " . number_format($remaining, 0, '.', ' ') . " FCFA"];
                                        });
                                })
                                ->hidden(fn() => $this->type === 'advance')
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state) {
                                    if ($state) {
                                        $payment = ClientPayment::find($state);
                                        $used = $payment->children()->sum('amount');
                                        $set('amount', $payment->amount - $used);
                                    }
                                })
                                ->columnSpanFull(),
                            TextInput::make('amount')
                                ->label(fn() => $this->type === 'advance' ? 'Montant de l\'avance' : 'Montant du règlement')
                                ->numeric()
                                ->required()
                                ->prefix('FCFA')
                                ->default(0)
                                ->live(debounce: 5000),
                            DatePicker::make('date')
                                ->label(fn() => $this->type === 'advance' ? 'Date de l\'avance' : 'Date du règlement')
                                ->required()
                                ->default(now()),
                            Select::make('payment_method')
                                ->label('Méthode de paiement')
                                ->options([
                                    'Espèces' => 'Espèces',
                                    'Chèque' => 'Chèque',
                                    'Virement' => 'Virement',
                                    'Autre' => 'Autre',
                                ])
                                ->required()
                                ->placeholder('Choisir une méthode'),
                            TextInput::make('reference')
                                ->label('Référence / N° de transaction')
                                ->placeholder('Ex: CHQ 123456'),
                            Textarea::make('note')
                                ->label('Notes / Observations')
                                ->columnSpanFull(),
                        ])->columns(2),
                    Step::make('Véhicules concernés')
                        ->hidden(fn () => $this->type === 'depot' || $this->type === 'advance')
                        ->schema([
                            Repeater::make('selected_items')
                                ->label('Sélectionner les chargements')
                                ->schema([
                                    Select::make('invoice_item_id')
                                        ->label('Chargement')
                                        ->options(function (Get $get) {
                                            $clientId = $get('../../client_id');
                                            if (!$clientId) return [];
                                            return InvoiceItem::where(function($query) use ($clientId) {
                                                $query->whereHas('invoice', fn($q) => $q->where('client_id', $clientId))
                                                      ->orWhereHas('delivery', fn($q) => $q->where('client_id', $clientId));
                                            })
                                                ->where('is_paid', false)
                                                ->whereNull('client_payment_id')
                                                ->with(['delivery', 'invoice'])
                                                ->get()
                                                ->mapWithKeys(fn($item) => [
                                                    $item->id => "Facture: " . ($item->invoice->number ?? 'N/A') . " - Véhicule: " . ($item->delivery->vehicle_registration ?? 'N/A') . " - Date: " . ($item->delivery->unload_date?->format('d/m/Y') ?? 'N/A') . " - Reste: " . number_format($item->total, 0, '.', ' ') . " FCFA"
                                                ]);
                                        })
                                        ->searchable()
                                        ->required()
                                        ->distinct()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            $item = InvoiceItem::find($state);
                                            if ($item) {
                                                $set('original_total', $item->total);
                                                $set('unit_price', $item->unit_price);
                                                $set('quantity_delivered', $item->quantity_delivered);
                                            }
                                        }),
                                    TextInput::make('missing_quantity')
                                        ->label('Manquant (Litres)')
                                        ->numeric()
                                        ->default(0)
                                        ->required()
                                        ->live(debounce: 5000)
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
                    Step::make('Récapitulatif')
                        ->hidden(fn () => $this->type === 'depot')
                        ->schema([
                            Placeholder::make('recap_amount')
                                ->label('Montant du règlement')
                                ->content(fn (Get $get) => number_format($get('amount') ?: 0, 0, '.', ' ') . ' FCFA'),
                            Placeholder::make('recap_items')
                                ->label('Chargements concernés')
                                ->content(function (Get $get) {
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
                                }),
                        ]),
                ])
                ->contained(false)
                ->submitAction(new \Illuminate\Support\HtmlString('<button type="submit" class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-primary fi-btn-color-primary bg-primary-600 text-white hover:bg-primary-500 focus-visible:ring-primary-500/50 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus-visible:ring-primary-400/50 gap-1.5 px-3 py-2 text-sm inline-grid">Enregistrer le règlement</button>'))
            ])
            ->statePath('data');
    }

    public function create()
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            // 1. Créer le paiement
            $payment = ClientPayment::create([
                'client_id' => $data['client_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'payment_type' => $this->type === 'advance' ? null : $this->type,
                'is_advance' => $this->type === 'advance',
                'amount' => $data['amount'],
                'date' => $data['date'],
                'payment_method' => $data['payment_method'] ?? 'Avance',
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? ($data['parent_id'] ? 'Règlement effectué via une avance' : null),
            ]);

            // 2. Mettre à jour les chargements (invoice_items) et les factures (invoices)
            if (!empty($data['selected_items'])) {
                foreach ($data['selected_items'] as $itemData) {
                    if (empty($itemData['invoice_item_id'])) continue;

                    $item = InvoiceItem::find($itemData['invoice_item_id']);
                    if ($item) {
                        $oldTotal = $item->total;
                        $newTotal = floatval($itemData['new_total']);
                        $missing = floatval($itemData['missing_quantity']);

                        // Mettre à jour l'item
                        $item->update([
                            'client_payment_id' => $payment->id,
                            'missing_quantity' => $missing,
                            'total' => $newTotal,
                            'is_paid' => true,
                        ]);

                        // Marquer le chargement comme payé
                        if ($item->delivery) {
                            $item->delivery->update([
                                'status' => \App\Enums\LoadStatus::Invoiced
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
        });

        Notification::make()
            ->title('Règlement enregistré et factures mises à jour')
            ->success()
            ->send();

        $this->dispatch('update-client');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.modals.client.add-client-payment');
    }
}
