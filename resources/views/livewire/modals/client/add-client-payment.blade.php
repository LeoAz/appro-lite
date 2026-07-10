<x-app.modal
    headerClasses="p-4 sm:px-6 sm:py-4 border-b border-secondary-100"
    contentClasses="relative p-6"
    footerClasses="hidden"
>
    <x-slot name="title">
        <div class="flex flex-col">
            <span class="text-xl font-bold text-slate-900">Enregistrement d'un règlement</span>
            <span class="text-sm font-normal text-slate-500 mt-1">Suivez les étapes pour imputer les paiements aux chargements</span>
        </div>
    </x-slot>

    <x-slot name="content">
        <div class="py-2">
            <form wire:submit="create">
                {{ $this->form }}
            </form>
        </div>
    </x-slot>

    <x-slot name="buttons">
        {{-- Les boutons sont gérés par le Wizard de Filament --}}
    </x-slot>
</x-app.modal>
