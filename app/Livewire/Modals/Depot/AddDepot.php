<?php

namespace App\Livewire\Modals\Depot;

use App\Models\Depot;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AddDepot extends ModalComponent implements HasForms
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
            ->schema([
                TextInput::make("name")->name("Nom")->required(),
                // ...
            ])
            ->statePath("data");
    }

    public function create()
    {
        Depot::create($this->form->getState());
        Notification::make()
            ->title("Nouveau dépôt ajouté")
            ->success()
            ->body("Le dépôt a été enregistré avec succés!")
            ->send();

        $this->dispatch("add-depot");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.modals.depot.add-depot");
    }
}
