<div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Suivi des Stocks</h1>
            <p class="text-sm text-gray-500 mt-1">Consultez l'état de vos dépôts et l'historique des mouvements.</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="min-w-[250px]">
                {{ $this->form }}
            </div>

            <button wire:click="downloadPdf" class="inline-flex items-center px-4 py-2 bg-gray-900 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-950 transition ease-in-out duration-150 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimer le rapport
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
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
                <button
                    @click="tab = 'depot_sales'"
                    :class="tab === 'depot_sales' ? 'border-primary-600 text-primary-600 bg-primary-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="px-6 py-4 text-sm font-medium border-b-2 transition-all duration-200"
                >
                    Historique des Ventes sur Dépôt
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
                                    <td class="py-4 text-gray-600">{{ $load->load_date->format('d/m/Y') }}</td>
                                    <td class="py-4 font-medium text-gray-900">{{ $load->vehicle_registration }}</td>
                                    <td class="py-4 text-gray-600">{{ $load->product }}</td>
                                    <td class="py-4 text-right font-bold text-gray-900">{{ number_format((float) ($load->volume ?? 0), 0, ',', ' ') }} L</td>
                                    <td class="py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $load->status === 'LIVRÉ' ? 'bg-green-100 text-green-700' : ($load->status === 'LIVRÉ ET PAYÉ' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700') }}">
                                            {{ $load->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 italic">Aucun mouvement enregistré.</td>
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
                                    <td class="py-4 text-right font-bold text-gray-900">{{ number_format((float) ($purchase->quantity ?? 0), 0, ',', ' ') }} L</td>
                                    <td class="py-4 text-right text-gray-600">{{ $purchase->unit_price ? number_format((float) $purchase->unit_price, 0, ',', ' ') . ' FCFA' : '-' }}</td>
                                    <td class="py-4 text-right font-medium text-gray-900">{{ $purchase->total_price ? number_format((float) $purchase->total_price, 0, ',', ' ') . ' FCFA' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 italic">Aucun mouvement enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Table Ventes sur Dépôt -->
                <div x-show="tab === 'depot_sales'" x-cloak>
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-gray-400 border-b border-gray-100">
                                <th class="pb-3 font-medium">Date</th>
                                <th class="pb-3 font-medium">Facture</th>
                                <th class="pb-3 font-medium">Client</th>
                                <th class="pb-3 font-medium">Produit</th>
                                <th class="pb-3 font-medium text-right">Quantité</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($this->getDepotSaleTableQuery()->get() as $item)
                                <tr>
                                    <td class="py-4 text-gray-600">{{ $item->depotInvoice->date->format('d/m/Y') }}</td>
                                    <td class="py-4 font-medium text-gray-900">{{ $item->depotInvoice->number }}</td>
                                    <td class="py-4 text-gray-600">{{ $item->depotInvoice->client->nom }}</td>
                                    <td class="py-4 text-gray-600">{{ $item->compartment->product }}</td>
                                    <td class="py-4 text-right font-bold text-gray-900">{{ number_format((float) $item->quantity, 0, ',', ' ') }} L</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 italic">Aucune vente enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
