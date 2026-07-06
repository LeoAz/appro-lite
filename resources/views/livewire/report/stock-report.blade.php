<div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Suivi des Stocks</h1>
            <p class="text-sm text-gray-500 mt-1">Consultez l'état de vos dépôts et l'historique des mouvements.</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-64">
                {{ $this->form }}
            </div>

            <div class="flex space-x-2">
                <button wire:click="downloadPdf" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 active:bg-gray-100 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    PDF
                </button>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-950 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimer
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 print:hidden">
        <!-- Section Stock par Compartiment -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">État des Compartiments</h3>
            </div>
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>

        <!-- Section Historique avec Tabs -->
        <div x-data="{ tab: 'loads' }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex border-b border-gray-100">
                <button
                    @click="tab = 'loads'"
                    :class="tab === 'loads' ? 'border-primary-600 text-primary-600 bg-primary-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="px-6 py-4 text-sm font-medium border-b-2 transition-all duration-200"
                >
                    Historique des Chargements
                </button>
                <button
                    @click="tab = 'purchases'"
                    :class="tab === 'purchases' ? 'border-primary-600 text-primary-600 bg-primary-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="px-6 py-4 text-sm font-medium border-b-2 transition-all duration-200"
                >
                    Historique des Achats
                </button>
            </div>

            <div class="p-6">
                <!-- Table Chargements -->
                <div x-show="tab === 'loads'" x-cloak>
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-100">
                                <th class="pb-3 font-medium">Date</th>
                                <th class="pb-3 font-medium">Camion</th>
                                <th class="pb-3 font-medium">Produit</th>
                                <th class="pb-3 font-medium text-right">Quantité</th>
                                <th class="pb-3 font-medium">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($this->getLoadTableQuery()->get() as $load)
                                <tr>
                                    <td class="py-4 text-gray-600">{{ $load->load_date->format('d/m/Y H:i') }}</td>
                                    <td class="py-4 font-medium text-gray-900">{{ $load->vehicle_registration }}</td>
                                    <td class="py-4 text-gray-600">{{ $load->product }}</td>
                                    <td class="py-4 text-right font-bold text-gray-900">{{ number_format($load->capacity, 0, ',', ' ') }} L</td>
                                    <td class="py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $load->status === 'LIVRÉ' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $load->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 italic">Aucun chargement enregistré pour ce dépôt.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Table Achats -->
                <div x-show="tab === 'purchases'" x-cloak>
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-100">
                                <th class="pb-3 font-medium">Date</th>
                                <th class="pb-3 font-medium">Produit</th>
                                <th class="pb-3 font-medium text-right">Quantité</th>
                                <th class="pb-3 font-medium text-right">Prix Unitaire</th>
                                <th class="pb-3 font-medium text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($this->getPurchaseTableQuery()->get() as $purchase)
                                <tr>
                                    <td class="py-4 text-gray-600">{{ $purchase->purchase_date?->format('d/m/Y') }}</td>
                                    <td class="py-4 font-medium text-gray-900">{{ $purchase->product }}</td>
                                    <td class="py-4 text-right font-bold text-blue-600">+{{ number_format($purchase->quantity, 0, ',', ' ') }} L</td>
                                    <td class="py-4 text-right text-gray-600">{{ $purchase->unit_price ? number_format($purchase->unit_price, 2, ',', ' ') . ' FCFA' : '-' }}</td>
                                    <td class="py-4 text-right font-medium text-gray-900">{{ $purchase->total_price ? number_format($purchase->total_price, 2, ',', ' ') . ' FCFA' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 italic">Aucun achat enregistré pour ce dépôt.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Mode Impression -->
    <div class="hidden print:block">
        @if($this->depot_id)
            @php $selectedDepot = \App\Models\Depot::find($this->depot_id); @endphp
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold">Rapport de Stock : {{ $selectedDepot?->name }}</h1>
                <p class="text-gray-500">Généré le {{ now()->format('d/m/Y H:i') }}</p>
            </div>

            <h2 class="text-xl font-bold mb-4">État des Compartiments</h2>
            @include('livewire.report.print-stock', ['compartments' => \App\Models\Compartment::where('depot_id', $this->depot_id)->get(), 'date' => now()])

            <div class="mt-8">
                <h2 class="text-xl font-bold mb-4">Historique des 20 derniers Chargements</h2>
                <table class="w-full border-collapse border border-gray-300 text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 p-2">Date</th>
                            <th class="border border-gray-300 p-2">Véhicule</th>
                            <th class="border border-gray-300 p-2">Produit</th>
                            <th class="border border-gray-300 p-2">Quantité</th>
                            <th class="border border-gray-300 p-2">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->getLoadTableQuery()->limit(20)->get() as $load)
                            <tr>
                                <td class="border border-gray-300 p-2">{{ $load->load_date->format('d/m/Y') }}</td>
                                <td class="border border-gray-300 p-2">{{ $load->vehicle_registration }}</td>
                                <td class="border border-gray-300 p-2">{{ $load->product }}</td>
                                <td class="border border-gray-300 p-2 text-right">{{ number_format($load->capacity, 0) }} L</td>
                                <td class="border border-gray-300 p-2">{{ $load->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
             @include('livewire.report.print-stock', ['compartments' => \App\Models\Compartment::all(), 'date' => now()])
        @endif
    </div>

    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .fi-header-actions, .fi-ta-header-actions, .fi-ta-filters, .fi-ta-header, .fi-ta-actions {
                display: none !important;
            }
            body { background: white !important; padding: 0 !important; }
            .p-6 { padding: 0 !important; }
            .rounded-xl, .shadow-sm, .border { border: none !important; box-shadow: none !important; }
        }
    </style>
</div>
