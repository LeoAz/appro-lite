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

    <div class="billing-label" style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">II. État des Créances (Chargements non payés)</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">N°</th>
                <th>N° Facture</th>
                <th>Date</th>
                <th>Véhicule</th>
                <th>Produit</th>
                <th class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @php $currentReceivable = 0; $receivableCount = 0; @endphp
            @foreach($receivables->where('is_paid', false) as $item)
                @php $receivableCount++; @endphp
                <tr>
                    <td class="text-gray">{{ $receivableCount }}</td>
                    <td class="font-bold text-gray">{{ $item->invoice->number }}</td>
                    <td class="text-gray">{{ $item->invoice->date->format('d/m/Y') }}</td>
                    <td class="text-gray">{{ $item->delivery?->vehicle_registration }}</td>
                    <td class="text-gray">{{ $item->delivery?->product }}</td>
                    <td class="text-right font-bold">{{ number_format($item->total, 0, '.', ' ') }} FCFA</td>
                </tr>
                @php $currentReceivable += $item->total; @endphp
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

    <div class="billing-label" style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">III. Historique des Paiements (Chargements payés)</div>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">N°</th>
                <th>N° Facture</th>
                <th>Date Fact.</th>
                <th>Véhicule</th>
                <th>Montant</th>
                <th>Date Paiement</th>
                <th class="text-right">Réf. Règlement</th>
            </tr>
        </thead>
        <tbody>
            @php $paidCount = 0; @endphp
            @foreach($receivables->where('is_paid', true) as $item)
                @php $paidCount++; @endphp
                <tr>
                    <td class="text-gray">{{ $paidCount }}</td>
                    <td class="font-bold text-gray">{{ $item->invoice->number }}</td>
                    <td class="text-gray">{{ $item->invoice->date->format('d/m/Y') }}</td>
                    <td class="text-gray">{{ $item->delivery?->vehicle_registration }}</td>
                    <td class="text-gray">{{ number_format($item->total, 0, '.', ' ') }} FCFA</td>
                    <td class="text-gray">{{ $item->payment?->date->format('d/m/Y') }}</td>
                    <td class="text-right font-bold">{{ $item->payment?->reference }}</td>
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
