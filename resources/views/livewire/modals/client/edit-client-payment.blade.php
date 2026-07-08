<x-app.modal
    headerClasses="p-4 sm:px-6 sm:py-4 border-b border-secondary-100"
    contentClasses="relative p-4 sm:px-6 sm:px-5"
    footerClasses="mt-4 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border border-t border-secondary-100"
>
    <x-slot name="title">
        <div class="flex flex-col">
            <span class="text-lg font-bold">Modifier le règlement</span>
            <span class="text-sm font-normal text-gray-500">Mettez à jour les informations du paiement</span>
        </div>
    </x-slot>

    <x-slot name="content">
        <div class="py-4">
            <form wire:submit="save">
                {{ $this->form }}
            </form>
        </div>
    </x-slot>

    <x-slot name="buttons">
        <div class="flex justify-end gap-x-3 w-full">
            <button type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    wire:click="$dispatch('closeModal')"
            >
                ANNULER
            </button>
            <button type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-slate-800 border border-transparent rounded-md shadow-sm hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500"
                    wire:click="save"
            >
                <div wire:loading.remove>
                    ENREGISTRER LES MODIFICATIONS
                </div>
                <div wire:loading>
                    <x-filament::loading-indicator class="h-5 w-5" />
                </div>
            </button>
        </div>
    </x-slot>
</x-app.modal>
