<div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">État des Créances</h1>
            <p class="text-sm text-gray-500 mt-1">Liste des chargements facturés en attente de règlement.</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="min-w-[250px]">
                {{ $this->form }}
            </div>

            <button wire:click="downloadPdf" class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-950 transition ease-in-out duration-150 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimer
            </button>
        </div>
    </div>

    <div>
        {{ $this->table }}
    </div>
</div>
