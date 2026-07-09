<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold">Modifier la Facture sur Dépôt</h3>
        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
    </div>

    <form wire:submit.prevent="update">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-x-4">
            <x-filament::button color="gray" wire:click="closeModal">
                Annuler
            </x-filament::button>
            <x-filament::button type="submit">
                Enregistrer les modifications
            </x-filament::button>
        </div>
    </form>
</div>
