<?php

namespace App\Livewire\Modals\Carrier;

use App\Models\Carrier;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class EditCarrier extends ModalComponent implements HasForms
{
    use InteractsWithForms;
    public Carrier $carrier;

    public ?array $data = [];

    public function mount(Carrier $carrier)
    {
        $this->carrier = $carrier;
        $this->form->fill($carrier->toArray());
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

    public function update()
    {
        $this->carrier->update($this->form->getState());
        Notification::make()
            ->title("Transporteur mise à jour")
            ->success()
            ->body("Le transpoteur a été mise à jour avec succés!")
            ->send();

        $this->dispatch("update-carrier");
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return "3xl";
    }

    public function render()
    {
        return view("livewire.modals.carrier.edit-carrier");
    }
}
