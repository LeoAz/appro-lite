<?php

namespace App\Livewire\Modals\Invoice;

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
            'items' => $invoice->items->map(fn ($item) => [
                'load_id' => $item->load_id,
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
                    ->unique('invoices', 'number', ignoreRecord: true),
                DatePicker::make('date')
                    ->label('Date')
                    ->required(),
                Select::make('client_id')
                    ->label('Client')
                    ->options(Client::pluck('nom', 'id'))
                    ->required()
                    ->disabled(),

                Repeater::make('items')
                    ->label('Livraisons')
                    ->schema([
                        Select::make('load_id')
                            ->label('Livraison')
                            ->options(function (Get $get) {
                                $clientId = $get('../../client_id');
                                if (!$clientId) return [];
                                return Load::where('client_id', $clientId)
                                    ->get()
                                    ->mapWithKeys(fn ($load) => [$load->id => "Ref: {$load->id} - Date: {$load->unload_date?->format('d/m/Y')} - Vol: {$load->volume}L"]);
                            })
                            ->required()
                            ->disabled(),
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
                    ->columns(5)
                    ->reorderable(false)
                    ->addable(false)
                    ->deletable(false)
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $this->updateInvoiceTotals($get, $set);
                    }),

                TextInput::make('total_missing')
                    ->label('Total Manquant')
                    ->numeric()
                    ->readOnly(),
                TextInput::make('total_amount')
                    ->label('Montant Total')
                    ->numeric()
                    ->readOnly(),
            ]);
    }

    protected function updateItemTotals(Get $get, Set $set)
    {
        $loadId = $get('load_id');
        $qtyDelivered = floatval($get('quantity_delivered') ?: 0);
        $unitPrice = floatval($get('unit_price') ?: 0);

        $load = Load::find($loadId);
        $missing = 0;
        if ($load) {
            $missing = floatval($load->volume) - $qtyDelivered;
        }

        $set('missing_quantity', $missing);
        $set('total', $qtyDelivered * $unitPrice);
    }

    protected function updateInvoiceTotals(Get $get, Set $set)
    {
        $items = $get('items') ?: [];
        $totalMissing = 0;
        $totalAmount = 0;

        foreach ($items as $item) {
            $totalMissing += floatval($item['missing_quantity'] ?: 0);
            $totalAmount += floatval($item['total'] ?: 0);
        }

        $set('total_missing', $totalMissing);
        $set('total_amount', $totalAmount);
    }

    public function update()
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            $this->invoice->update([
                'number' => $data['number'],
                'date' => $data['date'],
                'total_missing' => $data['total_missing'],
                'total_amount' => $data['total_amount'],
            ]);

            foreach ($data['items'] as $itemData) {
                $this->invoice->items()->where('load_id', $itemData['load_id'])->update([
                    'quantity_delivered' => $itemData['quantity_delivered'],
                    'unit_price' => $itemData['unit_price'],
                    'missing_quantity' => $itemData['missing_quantity'],
                    'total' => $itemData['total'],
                ]);
            }
        });

        Notification::make()
            ->title('Facture mise à jour')
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
        return view('livewire.modals.invoice.edit-invoice');
    }
}
