<x-app.modal
    headerClasses="p-4 sm:px-6 sm:py-4 border-b border-secondary-100"
    contentClasses="relative p-6"
    footerClasses="mt-4 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-secondary-100 bg-gray-50/50 rounded-b-lg"
>
    <x-slot name="title">
        <div class="flex flex-col">
            <span class="text-xl font-bold text-slate-900">Détails du règlement</span>
            <span class="text-sm font-normal text-slate-500 mt-1">Consultez ou modifiez les informations de ce paiement</span>
        </div>
    </x-slot>

    <x-slot name="content">
        <div class="py-2">
            <form wire:submit="save">
                {{ $this->form }}
            </form>
        </div>
    </x-slot>

    <x-slot name="buttons">
        <div class="flex justify-between items-center w-full">
            <button type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-semibold text-red-600 bg-white border border-red-200 rounded-lg shadow-sm hover:bg-red-50 hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all"
                    wire:confirm="Êtes-vous sûr de vouloir supprimer ce règlement ?"
                    wire:click="delete"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Supprimer
            </button>
            <div class="flex gap-x-3">
                <button type="button"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg shadow-sm hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-all"
                        wire:click="$dispatch('closeModal')"
                >
                    Fermer
                </button>
                @if($payment->is_advance || $payment->payment_type === 'depot')
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-slate-900 border border-transparent rounded-lg shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all"
                            wire:click="save"
                    >
                        <div wire:loading.remove>
                            Enregistrer
                        </div>
                        <div wire:loading>
                            <x-filament::loading-indicator class="h-5 w-5" />
                        </div>
                    </button>
                @endif

                @if($payment->is_advance)
                    <x-filament::dropdown>
                        <x-slot name="trigger">
                            <button type="button"
                                    class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 transition-all"
                            >
                                Convertir en règlement
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                        </x-slot>

                        <x-filament::dropdown.list>
                            <x-filament::dropdown.list.item wire:click="convertToPayment('load')">
                                Sur chargement
                            </x-filament::dropdown.list.item>
                            <x-filament::dropdown.list.item wire:click="convertToPayment('depot')">
                                Sur dépôt
                            </x-filament::dropdown.list.item>
                        </x-filament::dropdown.list>
                    </x-filament::dropdown>
                @endif
            </div>
        </div>
    </x-slot>
</x-app.modal>
