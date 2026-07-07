<?php

namespace App\Livewire\Modals\Invoice;

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
use LivewireUI\Modal\ModalComponent;

class ViewInvoice extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
        $this->form->fill([
            'number' => $invoice->number,
            'date' => $invoice->date,
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
                    ->disabled(),
                DatePicker::make('date')
                    ->label('Date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->disabled(),
                TextInput::make('client_name')
                    ->label('Client')
                    ->disabled(),
                TextInput::make('issuer_name')
                    ->label('Émetteur')
                    ->disabled(),

                Repeater::make('items')
                    ->label('Livraisons')
                    ->schema([
                        Select::make('load_id')
                            ->label('Livraison')
                            ->options(function (Get $get) {
                                $loadId = $get('load_id');
                                if (!$loadId) return [];
                                $load = Load::find($loadId);
                                if (!$load) return [];
                                return [$load->id => "Camion: " . ($load->vehicle->registration ?? $load->vehicle_registration ?? 'N/A') . " - Date: {$load->unload_date?->format('d/m/Y')} - Vol: {$load->volume}L"];
                            })
                            ->disabled(),
                        TextInput::make('bl_number')
                            ->label('N° BL')
                            ->disabled(),
                        TextInput::make('quantity_delivered')
                            ->label('Quantité livrée')
                            ->disabled(),
                        TextInput::make('unit_price')
                            ->label('Prix unitaire')
                            ->disabled(),
                        TextInput::make('missing_quantity')
                            ->label('Manquant')
                            ->disabled(),
                        TextInput::make('total')
                            ->label('Total')
                            ->disabled(),
                    ])
                    ->columns(6)
                    ->reorderable(false)
                    ->addable(false)
                    ->deletable(false)
                    ->itemLabel(function (array $state) {
                        if (!isset($state['load_id'])) return null;
                        $load = Load::find($state['load_id']);
                        $vehicle = $load->vehicle->registration ?? $load->vehicle_registration ?? 'N/A';
                        return "Véhicule: {$vehicle}";
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

                \Filament\Forms\Components\Hidden::make('total_missing'),
                \Filament\Forms\Components\Hidden::make('total_amount'),
            ]);
    }

    public static function modalMaxWidth(): string
    {
        return '7xl';
    }

    public function render()
    {
        return view('livewire.modals.invoice.view-invoice');
    }
}
