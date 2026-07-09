<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Rapport de Situation Client</h1>
                <p class="text-gray-600">Relevé de compte détaillé (Factures, Avances, Règlements)</p>
            </div>
            @if($client_id)
            <button wire:click="downloadPdf" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-md flex items-center gap-2 uppercase text-sm font-semibold transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimer le rapport
            </button>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="mb-8">
                <form>
                    {{ $this->form }}
                </form>
            </div>

            @if($client_id)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                <th class="py-4 px-2">Date</th>
                                <th class="py-4 px-2">Opération</th>
                                <th class="py-4 px-2 text-right">Débit</th>
                                <th class="py-4 px-2 text-right">Crédit</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @php
                                $totalDebit = 0;
                                $totalCredit = 0;
                                $reportLine = $transactions->firstWhere('type', 'report');
                                $openingBalance = $reportLine['credit'] ?? 0;
                            @endphp

                            @foreach($transactions as $transaction)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition">
                                    <td class="py-4 px-2 text-gray-600 font-medium">
                                        {{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}
                                    </td>
                                    <td class="py-4 px-2 {{ $transaction['type'] == 'report' ? 'font-bold text-gray-800' : 'text-gray-500' }}">
                                        @if($transaction['type'] == 'invoice')
                                            <a href="{{ route('invoices.print', $transaction['id']) }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                {{ $transaction['operation'] }}
                                            </a>
                                        @else
                                            {{ $transaction['operation'] }}
                                        @endif
                                    </td>
                                    <td class="py-4 px-2 text-right text-gray-700 font-semibold">
                                        {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 0, '.', ' ') : '-' }}
                                    </td>
                                    <td class="py-4 px-2 text-right text-gray-700 font-semibold">
                                        @if($transaction['type'] == 'report')
                                            {{ $transaction['credit'] != 0 ? number_format($transaction['credit'], 0, '.', ' ') : '-' }}
                                        @else
                                            {{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 0, '.', ' ') : '-' }}
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    if($transaction['type'] != 'report') {
                                        $totalDebit += $transaction['debit'];
                                        $totalCredit += $transaction['credit'];
                                    }
                                @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="py-6 px-2 text-right font-medium text-gray-500">Total Débit:</td>
                                <td class="py-6 px-2 text-right font-bold text-gray-800 text-lg">
                                    {{ number_format($totalDebit, 0, '.', ' ') }}
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="py-1 px-2 text-right font-medium text-gray-500">Total Crédit:</td>
                                <td class="py-1 px-2 text-right font-bold text-gray-800 text-lg">
                                    {{ number_format($totalCredit, 0, '.', ' ') }}
                                </td>
                                <td></td>
                            </tr>
                            @php
                                $finalBalance = $openingBalance + $totalDebit - $totalCredit;
                            @endphp
                            <tr>
                                <td colspan="2" class="py-8 px-2 text-right align-middle">
                                    <span class="text-xl font-bold text-blue-900 uppercase tracking-tight">Solde du compte:</span>
                                </td>
                                <td colspan="2" class="py-8 px-2 text-right align-middle">
                                    <span class="text-2xl font-bold {{ $finalBalance < 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($finalBalance, 0, '.', ' ') }} FCFA
                                    </span>
                                    <p class="text-[10px] text-gray-400 mt-1 italic">* Solde réel à ce jour. Un solde positif indique une dette du client.</p>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="py-20 text-center">
                    <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="text-gray-400 font-medium">Veuillez sélectionner un client pour afficher sa situation.</p>
                </div>
            @endif
        </div>
    </div>
</div>
