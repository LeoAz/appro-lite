<?php

namespace App\Livewire\Modals\Client;

use App\Models\Carrier;
use App\Models\Client;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class EditClient extends ModalComponent implements HasForms
{
    use InteractsWithForms;
    public Client $client;

    public ?array $data = [];

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->form->fill($client->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                TextInput::make("nom")->name("Nom")->required(),
                TextInput::make("contact")->label("Contact"),
                TextInput::make("initial_balance")
                    ->label("Solde Initial")
                    ->numeric()
                    ->default(0)
                    ->helperText("Solde positif si le client doit, négatif si l'entreprise doit au client."),
                Textarea::make("address")->label("Adresse")->columnSpan(2),
            ])
            ->statePath("data");
    }

    public function update()
    {
        $this->client->update($this->form->getState());
        Notification::make()
            ->title("Client mise à jour")
            ->success()
            ->body("Le client a été mise à jour avec succés!")
            ->send();

        $this->dispatch("update-client");
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return "3xl";
    }

    public function render()
    {
        return view("livewire.modals.client.edit-client");
    }
}
