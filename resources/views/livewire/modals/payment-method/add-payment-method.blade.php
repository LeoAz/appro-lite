<div class="p-6">
    <h2 class="text-lg font-bold mb-4">{{ $paymentMethod ? 'Modifier la méthode de règlement' : 'Ajouter une méthode de règlement' }}</h2>

    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-3">
            <x-secondary-button wire:click="closeModal">
                Annuler
            </x-secondary-button>

            <x-primary-button type="submit">
                {{ $paymentMethod ? 'Mettre à jour' : 'Enregistrer' }}
            </x-primary-button>
        </div>
    </form>
</div>
