<?php

namespace App\Livewire\Modals\Load;

use App\Enums\LoadStatus;
use App\Models\Client;
use App\Models\Load;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use LivewireUI\Modal\ModalComponent;

class AddUnload extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public Load $load;

    public $unload_date;
    public $unload_location;
    public $client_name;

    public function mount(?Load $load = null): void
    {
        $this->load = $load ?? new Load();

        $data = $this->load->toArray();
        if (empty($data['unload_date'])) {
            $data['unload_date'] = now();
        }

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                DatePicker::make("unload_date")
                    ->name("Date")
                    ->native(false)
                    ->displayFormat("d/m/Y")
                    ->default(now())
                    ->closeOnDateSelection()
                    ->required(),
                TextInput::make("unload_location")
                    ->label("Lieu livraison")
                    ->required(),
                TextInput::make("client_name")
                    ->label("Le client")
                    ->required(),
            ])
            //->statePath("data");
            ->model($this->load);
    }

    public function unload()
    {
        $attributes = $this->form->getState();
        $attributes = [
            ...$attributes,
            "status" => LoadStatus::Unloaded,
            "is_unload" => true,
        ];

        $this->load->update($attributes);
        Notification::make()
            ->title("Chargement mise à jour")
            ->success()
            ->body("Le chargement a été mise à jour avec succés!")
            ->send();

        $this->dispatch("update-load");
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return "5xl";
    }

    public function render()
    {
        return view("livewire.modals.load.add-unload");
    }
}
