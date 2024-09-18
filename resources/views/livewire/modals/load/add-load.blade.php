<x-app.modal
    headerClasses="p-4 sm:px-6 sm:py-4 border-b border-secondary-100"
    contentClasses="relative p-4 sm:px-6 sm:px-5"
    footerClasses="mt-4 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border border-t border-secondary-100"
>
    <x-slot name="title">
        <span>Ajouter un chargement</span>
    </x-slot>

    <x-slot name="content">
        <form wire:submit="create">
            {{ $this->form }}
        </form>
    </x-slot>

    <x-slot name="buttons">
        <button type="button" class="ml-4 inline-flex items-center gap-x-1.5 rounded-md bg-primary-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                wire:click="create"
        >
            <div wire:loading.remove>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="-ml-0.5 h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div wire:loading>
                <x-filament::loading-indicator class="h-5 w-5" />
            </div>
            Enregister
        </button>
        <button type="button" class="ml-7 inline-flex items-center gap-x-1.5 rounded-md bg-secondary-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-secondary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-600"
                wire:click="$dispatch('closeModal')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="-ml-0.5 h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Annuler
        </button>
    </x-slot>

</x-app.modal>
