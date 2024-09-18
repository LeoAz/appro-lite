<div class="p-6 text-gray-900">
    <div class="pb-2 space-y-3 sm:flex sm:items-center sm:justify-between sm:space-x-4 sm:space-y-0">

        <div>
            <button type="button" class="mb-3 inline-flex items-center gap-x-1.5 rounded-md bg-primary-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                    wire:click="$dispatch('openModal', {component:'modals.depot.add-depot'})"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="-ml-0.5 h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Ajouter un dépôt
            </button>
        </div>
    </div>
    <livewire:depot.listdeport/>
</div>
