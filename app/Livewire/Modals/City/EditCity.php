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

class EditCity extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public City $city;

    public ?array $data = [];

    public function mount(City $city)
    {
        $this->city = $city;
        $this->form->fill($city->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([TextInput::make("name")->name("Nom")->required()])
            ->statePath("data");
    }

    public function update()
    {
        $this->city->update($this->form->getState());
        Notification::make()
            ->title("Ville mise à jour")
            ->success()
            ->body("La ville a été mise à jour avec succés!")
            ->send();

        $this->dispatch("update-city");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.modals.city.edit-city");
    }
}
