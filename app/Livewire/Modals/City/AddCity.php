<?php

namespace App\Livewire\Modals\City;

use App\Models\City;
use App\Models\Depot;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AddCity extends ModalComponent implements HasForms
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
        City::create($this->form->getState());
        Notification::make()
            ->title("Nouvelle ville ajoutée")
            ->success()
            ->body("La ville a été enregistré avec succés!")
            ->send();

        $this->dispatch("add-city");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.modals.city.add-city");
    }
}
