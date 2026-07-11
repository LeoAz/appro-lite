<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de Situation Client - {{ $client->nom }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 40px;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
        }
        .issuer-info {
            text-align: right;
            font-size: 10px;
        }
        .issuer-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .qrcode-container {
            float: right;
            text-align: right;
            margin-top: -10px;
        }
        .divider {
            border-bottom: 1px solid #3b82f6;
            margin: 20px 0;
        }
        .report-title {
            color: #1e40af;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .report-subtitle {
            color: #666;
            font-size: 10px;
            margin-bottom: 20px;
        }
        .billing-info {
            width: 100%;
            margin-bottom: 30px;
        }
        .billing-info td {
            width: 50%;
            vertical-align: top;
        }
        .billing-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 9px;
        }
        .billing-value {
            font-weight: bold;
            color: #1e40af;
            font-size: 12px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            text-align: left;
            padding: 10px 5px;
            border-bottom: 2px solid #eee;
            color: #999;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        .items-table td {
            padding: 12px 5px;
            border-bottom: 1px solid #f9f9f9;
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-gray {
            color: #666;
        }
        .totals-section {
            width: 100%;
            margin-top: 20px;
        }
        .totals-table {
            width: 300px;
            float: right;
        }
        .totals-table td {
            padding: 5px;
        }
        .balance-row td {
            padding-top: 20px;
        }
        .balance-label {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
        }
        .balance-value {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
        }
        .text-red {
            color: #dc2626;
        }
        .text-green {
            color: #16a34a;
        }
        .status-badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            clear: both;
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 60%;">
                    <div class="report-title">Rapport de Situation Client</div>
                    <div class="report-subtitle">Relevé de compte et État des créances</div>
                </td>
                <td style="width: 40%;" class="issuer-info">
                    <div class="issuer-name">SOCIETE DE TRANSPORT</div>
                    @php
                        $qrData = "Rapport Situation Client: " . $client->nom . "\n" .
                                  "Période: Du " . \Carbon\Carbon::parse($date_from)->format('d/m/Y') . " Au " . \Carbon\Carbon::parse($date_to)->format('d/m/Y') . "\n" .
                                  "Solde: " . number_format($finalBalance, 0, '.', ' ') . " FCFA";
                    @endphp
                    <div class="qrcode-container">
                        <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(70)->generate($qrData)) !!} ">
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="billing-info">
        <tr>
            <td>
                <div class="billing-label">Client</div>
                <div class="billing-value">{{ $client->nom }}</div>
                <div class="text-gray">{{ $client->contact }}</div>
            </td>
            <td class="text-right">
                <div class="billing-label">Période du relevé</div>
                <div class="font-bold">Du {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }} Au {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="billing-label" style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">I. Relevé de compte détaillé</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="15%">Date</th>
                <th width="55%">Opération</th>
                <th width="15%" class="text-right">Débit</th>
                <th width="15%" class="text-right">Crédit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td class="text-gray">{{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}</td>
                <td class="{{ $transaction['type'] == 'report' || $transaction['type'] == 'advance' || $transaction['type'] == 'payment_via_advance' ? 'font-bold' : 'text-gray' }}" style="{{ $transaction['type'] == 'advance' ? 'color: #2563eb; font-style: italic;' : ($transaction['type'] == 'payment_via_advance' ? 'color: #0d9488; font-style: italic;' : '') }}">
                        {{ $transaction['operation'] }}
                    </td>
                    <td class="text-right font-bold">
                        {{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 0, '.', ' ') : '-' }}
                    </td>
                    <td class="text-right font-bold">
                        @if($transaction['type'] == 'report')
                            {{ $transaction['credit'] != 0 ? number_format($transaction['credit'], 0, '.', ' ') : '-' }}
                        @else
                            {{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 0, '.', ' ') : '-' }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="text-gray font-bold">Total Débit:</td>
                <td class="text-right font-bold" style="font-size: 14px;">{{ number_format($totalDebit, 0, '.', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-gray font-bold">Total Crédit:</td>
                <td class="text-right font-bold" style="font-size: 14px;">{{ number_format($totalCredit, 0, '.', ' ') }}</td>
            </tr>
            <tr class="balance-row">
                <td class="balance-label">Solde du compte:</td>
                <td class="balance-value {{ $finalBalance < 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($finalBalance, 0, '.', ' ') }} FCFA
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both; margin-bottom: 40px;"></div>

    @php
        $allPayments = \App\Models\ClientPayment::where('client_id', $client->id)
            ->whereBetween('date', [$date_from, $date_to])
            ->orderBy('date', 'desc')
            ->get();
    @endphp

    <div class="billing-label" style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">II. Récapitulatif des Règlements (Période du relevé)</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">N°</th>
                <th width="15%">Date</th>
                <th width="20%">Référence</th>
                <th width="20%">Mode</th>
                <th width="25%">Type / Nature</th>
                <th width="15%" class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @php $payCount = 0; $totalPay = 0; @endphp
            @foreach($allPayments as $payment)
                @php
                    $payCount++;
                    if (!$payment->parent_id) {
                        $totalPay += $payment->amount;
                    }

                    $nature = "";
                    if ($payment->is_advance) {
                        $nature = "Avance";
                    } elseif ($payment->parent_id) {
                        $nature = "Règlement via Avance";
                    } else {
                        $nature = $payment->payment_type === 'depot' ? 'Règlement Dépôt' : 'Règlement Chargement';
                    }
                @endphp
                <tr>
                    <td class="text-gray">{{ $payCount }}</td>
                    <td class="text-gray">{{ $payment->date->format('d/m/Y') }}</td>
                    <td class="font-bold text-gray">{{ $payment->reference }}</td>
                    <td class="text-gray">{{ $payment->payment_method ?? '-' }}</td>
                    <td class="text-gray">
                        <span style="{{ $payment->is_advance ? 'color: #2563eb;' : ($payment->parent_id ? 'color: #0d9488;' : '') }}">
                            {{ $nature }}
                        </span>
                    </td>
                    <td class="text-right font-bold">
                        {{ number_format($payment->amount, 0, '.', ' ') }} FCFA
                    </td>
                </tr>
            @endforeach
            @if($payCount === 0)
                <tr>
                    <td colspan="6" class="text-center text-gray">Aucun règlement enregistré sur cette période.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr class="balance-row">
                <td class="balance-label">Total Encaissé*:</td>
                <td class="balance-value text-green">
                    {{ number_format($totalPay, 0, '.', ' ') }} FCFA
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-right text-gray" style="font-size: 8px; font-style: italic;">
                    * Hors règlements via utilisation d'avances pour éviter les doubles comptes.
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both; margin-bottom: 40px;"></div>

    @php
        $allReceivables = $receivables->where('is_paid', false)->map(function($item) {
            return [
                'number' => $item->invoice->number ?? '-',
                'date' => $item->invoice->date,
                'reference' => $item->delivery?->vehicle_registration ?? '-',
                'product' => $item->delivery?->product ?? '-',
                'total' => $item->total,
                'type' => 'load'
            ];
        });

        $depotReceivablesItems = \App\Models\DepotInvoiceItem::whereHas('depotInvoice', function($q) use ($client) {
            $q->where('client_id', $client->id);
        })->where('is_paid', false)->with(['depotInvoice', 'compartment'])->get();

        foreach($depotReceivablesItems as $depotItem) {
            $allReceivables->push([
                'number' => $depotItem->depotInvoice->number ?? '-',
                'date' => $depotItem->depotInvoice->date,
                'reference' => $depotItem->depotInvoice->depot->name ?? '-',
                'product' => $depotItem->compartment->product ?? '-',
                'total' => $depotItem->total,
                'type' => 'depot'
            ]);
        }
        $allReceivables = $allReceivables->sortBy('date');
    @endphp

    <div class="billing-label" style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">III. État des Créances (Chargements et Dépôts non payés)</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">N°</th>
                <th>N° Facture</th>
                <th>Date</th>
                <th>Véhicule / Dépôt</th>
                <th>Produit</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @php $currentReceivable = 0; $receivableCount = 0; @endphp
            @foreach($allReceivables as $receivableRow)
                @php $receivableCount++; @endphp
                <tr>
                    <td class="text-gray">{{ $receivableCount }}</td>
                    <td class="font-bold text-gray">{{ $receivableRow['number'] }}</td>
                    <td class="text-gray">{{ $receivableRow['date']->format('d/m/Y') }}</td>
                    <td class="text-gray">{{ $receivableRow['reference'] }}</td>
                    <td class="text-gray">{{ $receivableRow['product'] }}</td>
                    <td class="text-right font-bold">{{ number_format($receivableRow['total'], 0, '.', ' ') }} FCFA</td>
                </tr>
                @php $currentReceivable += $receivableRow['total']; @endphp
            @endforeach
            @if($receivableCount === 0)
                <tr>
                    <td colspan="6" class="text-center text-gray">Aucune créance en cours.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr class="balance-row">
                <td class="balance-label">Total Restant Dû:</td>
                <td class="balance-value text-red">
                    {{ number_format($currentReceivable, 0, '.', ' ') }} FCFA
                </td>
            </tr>
        </table>
    </div>

    <div style="clear: both; margin-bottom: 40px;"></div>

    @php
        $allPaidItems = $receivables->where('is_paid', true)->map(function($item) {
            return [
                'number' => $item->invoice->number ?? '-',
                'date' => $item->invoice->date,
                'reference' => $item->delivery?->vehicle_registration ?? '-',
                'total' => $item->total,
                'payment_date' => $item->payment?->date,
                'payment_ref' => $item->payment?->reference,
                'type' => 'load'
            ];
        });

        $depotPaidItems = \App\Models\DepotInvoiceItem::whereHas('depotInvoice', function($q) use ($client) {
            $q->where('client_id', $client->id);
        })->where('is_paid', true)->with(['depotInvoice', 'payment'])->get();

        foreach($depotPaidItems as $paidItem) {
            $allPaidItems->push([
                'number' => $paidItem->depotInvoice->number ?? '-',
                'date' => $paidItem->depotInvoice->date,
                'reference' => $paidItem->depotInvoice->depot->name ?? '-',
                'total' => $paidItem->total,
                'payment_date' => $paidItem->payment?->date,
                'payment_ref' => $paidItem->payment?->reference,
                'type' => 'depot'
            ]);
        }
        $allPaidItems = $allPaidItems->sortByDesc('payment_date');
    @endphp

    <div class="billing-label" style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">IV. Historique des Paiements (Chargements et Dépôts payés)</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">N°</th>
                <th>N° Facture</th>
                <th>Date Fact.</th>
                <th>Véhicule / Dépôt</th>
                <th>Montant</th>
                <th>Date Paiement</th>
                <th class="text-right">Réf. Règlement</th>
            </tr>
        </thead>
        <tbody>
            @php $paidCount = 0; @endphp
            @foreach($allPaidItems as $itemRow)
                @php $paidCount++; @endphp
                <tr>
                    <td class="text-gray">{{ $paidCount }}</td>
                    <td class="font-bold text-gray">{{ $itemRow['number'] }}</td>
                    <td class="text-gray">{{ $itemRow['date']->format('d/m/Y') }}</td>
                    <td class="text-gray">{{ $itemRow['reference'] }}</td>
                    <td class="text-gray">{{ number_format($itemRow['total'], 0, '.', ' ') }} FCFA</td>
                    <td class="text-gray">{{ $itemRow['payment_date']?->format('d/m/Y') ?? '-' }}</td>
                    <td class="text-right font-bold">{{ $itemRow['payment_ref'] ?? '-' }}</td>
                </tr>
            @endforeach
            @if($paidCount === 0)
                <tr>
                    <td colspan="7" class="text-center text-gray">Aucun historique de paiement.</td>
                </tr>
            @endif
        </tbody>
    </table>


    <div class="footer">
        Document généré le {{ $date->format('d/m/Y H:i') }}<br>
        © {{ date('Y') }} Application de Gestion de Transport.
    </div>
</body>
</html>
