<?php

namespace App\Livewire\Modals\PaymentMethod;

use App\Models\PaymentMethod;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use LivewireUI\Modal\ModalComponent;

class AddPaymentMethod extends ModalComponent implements HasForms
{
    use InteractsWithForms;

    public ?PaymentMethod $paymentMethod = null;
    public ?array $data = [];

    public function mount(?PaymentMethod $paymentMethod = null): void
    {
        $this->paymentMethod = $paymentMethod;

        if ($this->paymentMethod) {
            $this->form->fill($this->paymentMethod->toArray());
        } else {
            $this->form->fill();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nom de la méthode')
                    ->required()
                    ->unique(PaymentMethod::class, 'name', ignorable: $this->paymentMethod)
                    ->placeholder('Ex: Mobile Money, Espèces, etc.'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if ($this->paymentMethod) {
            $this->paymentMethod->update($data);
            $message = 'Méthode de règlement modifiée avec succès.';
        } else {
            PaymentMethod::create($data);
            $message = 'Méthode de règlement ajoutée avec succès.';
        }

        Notification::make()
            ->title($message)
            ->success()
            ->send();

        $this->dispatch('payment-method-updated');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.modals.payment-method.add-payment-method');
    }
}
