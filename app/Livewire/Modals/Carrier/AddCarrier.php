<?php

namespace App\Livewire\Modals\Carrier;

use App\Models\Carrier;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AddCarrier extends ModalComponent implements HasForms
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
                TextInput::make("contact")->label("Contact")->required(),
                Textarea::make("address")->label("Adresse")->columnSpan(2),
                // ...
            ])
            ->statePath("data");
    }

    public function create()
    {
        Carrier::create($this->form->getState());
        Notification::make()
            ->title("Nouveau transporteur ajouté")
            ->success()
            ->body("Le transporteur a été enregistré avec succés!")
            ->send();

        $this->dispatch("add-carrier");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.modals.carrier.add-carrier");
    }
}
