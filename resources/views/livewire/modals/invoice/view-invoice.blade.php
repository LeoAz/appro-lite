<x-app.modal
    headerClasses="p-4 sm:px-6 sm:py-4 border-b border-secondary-100"
    contentClasses="relative p-4 sm:px-6 sm:px-5"
    footerClasses="mt-4 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border border-t border-secondary-100"
>
    <x-slot name="title">
        <span>Consulter la facture : {{ $invoice->number }}</span>
    </x-slot>

    <x-slot name="content">
        <div class="space-y-4">
            {{ $this->form }}
        </div>
        <x-filament-actions::modals />
    </x-slot>

    <x-slot name="buttons">
        <button type="button" class="ml-4 inline-flex items-center gap-x-1.5 rounded-md bg-primary-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600"
                onclick="window.open('{{ route('invoices.print', $invoice) }}', '_blank')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="-ml-0.5 h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.821V21m0 0h10.56m-10.56 0V13.821m10.56 0V21m-10.56-7.179c0-1.18.91-2.164 2.09-2.201a51.964 51.964 0 0 1 6.38 0c1.18.037 2.09 1.022 2.09 2.201m0 7.179V13.821m0 0c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-6.38 0c-1.18.037-2.09 1.022-2.09 2.201m0 0V6.75m0 0h10.56V13.821" />
            </svg>
            Imprimer
        </button>
        <button type="button" class="ml-7 inline-flex items-center gap-x-1.5 rounded-md bg-secondary-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-secondary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-600"
                wire:click="$dispatch('closeModal')"
        >
            Fermer
        </button>
    </x-slot>

</x-app.modal>
