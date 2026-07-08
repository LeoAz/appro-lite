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
use LivewireUI\Modal\ModalComponent;

class AddClientPayment extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Client $client;
    public ?array $data = [];

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->form->fill([
            'date' => now(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Date')
                    ->required()
                    ->default(now()),
                TextInput::make('amount')
                    ->label('Montant de l\'avance')
                    ->numeric()
                    ->required()
                    ->prefix('FCFA'),
                TextInput::make('reference')
                    ->label('Référence')
                    ->placeholder('N° de chèque, virement, etc.'),
                Textarea::make('note')
                    ->label('Note')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function create()
    {
        $data = $this->form->getState();
        $data['client_id'] = $this->client->id;

        ClientPayment::create($data);

        Notification::make()
            ->title('Avance enregistrée')
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
