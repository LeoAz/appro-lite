<div class="p-6">
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

    <div class="mb-4 flex justify-end">
        <a href="{{ route('client.account.pdf', $client->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            Imprimer (PDF)
        </a>
    </div>

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
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php $runningBalance = 0; @endphp
                @foreach($history as $item)
                    @php $runningBalance += ($item['debit'] - $item['credit']); @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item['type'] == 'Facture' ? 'bg-blue-100 text-blue-800' : ($item['type'] == 'Solde Initial' ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800') }}">
                                {{ $item['type'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $item['reference'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $item['description'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ $item['debit'] > 0 ? number_format($item['debit'], 0, '.', ' ') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ $item['credit'] > 0 ? number_format($item['credit'], 0, '.', ' ') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $runningBalance < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($runningBalance, 0, '.', ' ') }}
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
                </tr>
            </tfoot>
        </table>
    </div>
</div>
