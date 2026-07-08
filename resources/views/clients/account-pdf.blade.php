<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Relevé de Compte - {{ $client->nom }}</title>
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
        .divider {
            border-bottom: 1px solid #3b82f6;
            margin: 20px 0;
        }
        .report-title {
            color: #1e40af;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .date-info {
            margin-bottom: 30px;
        }
        .date-info span {
            color: #1e40af;
            font-weight: bold;
        }
        .billing-info {
            width: 100%;
            margin-bottom: 40px;
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
            font-size: 10px;
        }
        .billing-value {
            font-weight: bold;
            color: #1e40af;
            font-size: 12px;
        }
        .billing-address {
            font-size: 10px;
            margin-top: 2px;
        }
        .summary-box {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
        }
        .summary-box table {
            width: 100%;
        }
        .summary-label {
            font-weight: bold;
            font-size: 12px;
        }
        .summary-value {
            font-weight: bold;
            font-size: 16px;
            text-align: right;
        }
        .text-red {
            color: #dc2626;
        }
        .text-green {
            color: #16a34a;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 8px;
            border: 1px solid #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        .items-table td {
            padding: 8px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div class="report-title">RELEVÉ DE COMPTE CLIENT</div>
                    <div class="date-info">Édité le : <span>{{ now()->format('d/m/Y H:i') }}</span></div>
                </td>
                <td style="width: 50%;" class="issuer-info">
                    <div class="issuer-name">SOCIETE DE TRANSPORT</div>
                    <div>Adresse de la société</div>
                    <div>Contact: +225 00 00 00 00 00</div>
                    <div>Email: contact@societe.com</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <table class="billing-info">
        <tr>
            <td>
                <div class="billing-label">Client</div>
                <div class="billing-value">{{ $client->nom }}</div>
                <div class="billing-address">
                    {{ $client->contact }}<br>
                    {{ $client->address }}
                </div>
            </td>
            <td>
                <div class="summary-box">
                    <table>
                        <tr>
                            <td class="summary-label">SOLDE ACTUEL</td>
                            <td class="summary-value {{ $client->balance < 0 ? 'text-red' : 'text-green' }}">
                                {{ number_format($client->balance, 0, '.', ' ') }} FCFA
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Opération</th>
                <th>Référence</th>
                <th>Description</th>
                <th class="text-right">Débit (+)</th>
                <th class="text-right">Crédit (-)</th>
                <th class="text-right">Solde</th>
            </tr>
        </thead>
        <tbody>
            @php $runningBalance = 0; @endphp
            @foreach($history as $item)
                @php $runningBalance += ($item->debit - $item->credit); @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                    <td>{{ $item->type }}</td>
                    <td>{{ $item->reference }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->debit > 0 ? number_format($item->debit, 0, '.', ' ') : '-' }}</td>
                    <td class="text-right">{{ $item->credit > 0 ? number_format($item->credit, 0, '.', ' ') : '-' }}</td>
                    <td class="text-right font-bold {{ $runningBalance < 0 ? 'text-red' : 'text-green' }}">
                        {{ number_format($runningBalance, 0, '.', ' ') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f3f4f6;">
                <td colspan="4" class="text-right font-bold">TOTAL</td>
                <td class="text-right font-bold">{{ number_format($history->sum('debit'), 0, '.', ' ') }}</td>
                <td class="text-right font-bold">{{ number_format($history->sum('credit'), 0, '.', ' ') }}</td>
                <td class="text-right font-bold {{ $client->balance < 0 ? 'text-red' : 'text-green' }}">
                    {{ number_format($client->balance, 0, '.', ' ') }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Document généré par l'application de gestion de transport.<br>
        Merci de votre confiance.
    </div>
</body>
</html>
