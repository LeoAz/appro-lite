<?php

namespace App\Livewire\Modals\Client;

use App\Models\Client;
use App\Models\ClientPayment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->label('Client concerné')
                    ->options(Client::all()->pluck('nom', 'id'))
                    ->searchable()
                    ->required()
                    ->disabled() // Usually we don't change the client on edit
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
        $this->payment->delete();

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
