<?php

namespace App\Livewire\Modals\Client;

use App\Models\Client;
use App\Models\ClientPayment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
        $this->form->fill($payment->toArray());
    }

    public static function modalMaxWidth(): string
    {
        return 'xl';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('payment_type')
                    ->label('Type de règlement')
                    ->options([
                        'load' => 'Règlement sur chargement',
                        'depot' => 'Règlement sur dépôt',
                    ])
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
                    ->label('Montant du règlement')
                    ->numeric()
                    ->required()
                    ->prefix('FCFA'),
                DatePicker::make('date')
                    ->label('Date du règlement')
                    ->required(),
                Select::make('payment_method')
                    ->label('Méthode de paiement')
                    ->options([
                        'Espèces' => 'Espèces',
                        'Chèque' => 'Chèque',
                        'Virement' => 'Virement',
                        'Autre' => 'Autre',
                    ])
                    ->required(),
                TextInput::make('reference')
                    ->label('Référence / N° de transaction')
                    ->placeholder('Ex: CHQ 123456'),
                Textarea::make('note')
                    ->label('Notes / Observations')
                    ->columnSpanFull(),

                Placeholder::make('items_summary')
                    ->label('Chargements liés')
                    ->visible(fn() => $this->payment->payment_type === 'load' && $this->payment->invoiceItems()->count() > 0)
                    ->content(function() {
                        $items = $this->payment->invoiceItems()->with(['delivery', 'invoice'])->get();
                        $summary = [];
                        foreach ($items as $item) {
                            $summary[] = "- Facture {$item->invoice->number} ({$item->delivery->vehicle_registration}) : " . number_format($item->total, 0, '.', ' ') . " FCFA (Manquant: {$item->missing_quantity}L)";
                        }
                        return new \Illuminate\Support\HtmlString(implode('<br>', $summary));
                    })
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save()
    {
        $data = $this->form->getState();

        $this->payment->update($data);

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
}
