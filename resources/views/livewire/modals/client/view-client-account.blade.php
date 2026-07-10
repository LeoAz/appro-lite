<div class="{{ $isModal ? 'p-6' : 'py-12 max-w-7xl mx-auto sm:px-6 lg:px-8' }}">
    <div class="{{ !$isModal ? 'bg-white overflow-hidden shadow-xl sm:rounded-lg p-6' : '' }}">
        <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Compte Client : {{ $client->nom }}</h2>
            <p class="text-gray-600">{{ $client->contact }} | {{ $client->address }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500 uppercase font-semibold">Solde Actuel</p>
            <p class="text-3xl font-black {{ $client->balance < 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($client->balance, 0, '.', ' ') }} FCFA
            </p>
        </div>
    </div>

    <div class="mb-6 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500">
            <li class="mr-2">
                <button wire:click="setActiveTab('history')" class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'history' ? 'text-blue-600 border-blue-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    Historique Global
                </button>
            </li>
            <li class="mr-2">
                <button wire:click="setActiveTab('invoices')" class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'invoices' ? 'text-blue-600 border-blue-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    Factures
                </button>
            </li>
            <li class="mr-2">
                <button wire:click="setActiveTab('payments')" class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'payments' ? 'text-blue-600 border-blue-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                    Règlements / Avances
                </button>
            </li>
        </ul>
    </div>

    <div class="mb-4 flex justify-between items-center">
        <div>
            <button wire:click="$dispatch('openModal', { component: 'modals.client.add-client-payment', arguments: { client: {{ $client->id }} } })" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Ajouter un règlement
            </button>
        </div>
        @if($activeTab === 'history')
        <a href="{{ route('client.account.pdf', $client->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            Imprimer (PDF)
        </a>
        @endif
    </div>

    @if($activeTab === 'history')
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Débit (+)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Crédit (-)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Solde Progressif</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php $runningBalance = 0; @endphp
                @foreach($history as $item)
                    @php $runningBalance += ($item->debit - $item->credit); @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->type == 'Facture' ? 'bg-blue-100 text-blue-800' : ($item->type == 'Solde Initial' ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800') }}">
                                {{ $item->type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $item->reference }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $item->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ $item->debit > 0 ? number_format($item->debit, 0, '.', ' ') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ $item->credit > 0 ? number_format($item->credit, 0, '.', ' ') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $runningBalance < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($runningBalance, 0, '.', ' ') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                            @if($item->is_payment)
                            <div class="flex justify-end gap-2">
                                <button wire:click="$dispatch('openModal', { component: 'modals.client.edit-client-payment', arguments: { payment: {{ $item->id }} } })" class="text-blue-600 hover:text-blue-900">
                                    <x-heroicon-m-pencil-square class="w-5 h-5"/>
                                </button>
                                <button wire:click="deletePayment({{ $item->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce règlement ?" class="text-red-600 hover:text-red-900">
                                    <x-heroicon-m-trash class="w-5 h-5"/>
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 font-bold">
                <tr>
                    <td colspan="4" class="px-6 py-4 text-right text-sm uppercase">Total</td>
                    <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($history->sum('debit'), 0, '.', ' ') }}</td>
                    <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($history->sum('credit'), 0, '.', ' ') }}</td>
                    <td class="px-6 py-4 text-right text-sm {{ $client->balance < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($client->balance, 0, '.', ' ') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    @if($activeTab === 'invoices')
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Facture</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $invoice->number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                Facturé
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">
                            {{ number_format($invoice->total_amount, 0, '.', ' ') }} FCFA
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Aucune facture trouvée</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    @if($activeTab === 'payments')
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($payments as $payment)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($payment->date)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $payment->reference ?: '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_method }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->note ?: 'Avance' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-600">
                            {{ number_format($payment->amount, 0, '.', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                            <div class="flex justify-end gap-2">
                                <button wire:click="$dispatch('openModal', { component: 'modals.client.edit-client-payment', arguments: { payment: {{ $payment->id }} } })" class="text-blue-600 hover:text-blue-900">
                                    <x-heroicon-m-pencil-square class="w-5 h-5"/>
                                </button>
                                <button wire:click="deletePayment({{ $payment->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce règlement ?" class="text-red-600 hover:text-red-900">
                                    <x-heroicon-m-trash class="w-5 h-5"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucun règlement trouvé</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
    </div>
</div>
