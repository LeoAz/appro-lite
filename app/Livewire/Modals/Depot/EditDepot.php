<?php

namespace App\Livewire\Modals\Depot;

use App\Models\Depot;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class EditDepot extends ModalComponent implements HasForms
{
    use InteractsWithForms;
    public Depot $depot;

    public ?array $data = [];

    public function mount(Depot $depot)
    {
        $this->depot = $depot;
        $this->form->fill($depot->toArray());
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

    public function update()
    {
        $this->depot->update($this->form->getState());
        Notification::make()
            ->title("Dépôt mise à jour")
            ->success()
            ->body("Le Dépôt a été mise à jour avec succés!")
            ->send();

        $this->dispatch("update-depot");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.modals.depot.edit-depot");
    }
}
