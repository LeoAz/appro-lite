<?php

namespace App\Livewire\Modals\Client;

use App\Models\Carrier;
use App\Models\Client;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AddClient extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
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

    public function create()
    {
        Client::create($this->form->getState());
        Notification::make()
            ->title("Nouveau client ajouté")
            ->success()
            ->body("Le client a été enregistré avec succés!")
            ->send();

        $this->dispatch("add-client");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.modals.client.add-client");
    }
}
