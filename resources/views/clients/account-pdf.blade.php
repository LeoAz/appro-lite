<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Compte Client - {{ $client->nom }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
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
        .title {
            color: #1e40af;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .client-info {
            margin-bottom: 30px;
        }
        .client-info span {
            font-weight: bold;
            color: #1e40af;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f3f4f6;
            text-align: center;
            padding: 8px;
            border: 1px solid #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        .items-table td {
            padding: 6px 8px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .totals-section {
            width: 100%;
            margin-top: 20px;
        }
        .totals-table {
            float: right;
            width: 40%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px;
            border: 1px solid #333;
        }
        .balance-row {
            background-color: #f3f4f6;
        }
        .negative {
            color: #dc2626;
        }
        .positive {
            color: #16a34a;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="issuer-name">CORRIDOR PETROLEUM</div>
                    <div style="font-size: 10px;">IMPORT - EXPORT - TRANSPORT - HYDROCARBURES</div>
                </td>
                <td class="issuer-info">
                    <div class="issuer-name">CORRIDOR PETROLEUM</div>
                    <div>BAMAKO, MALI</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <div class="title">RELEVÉ DE COMPTE CLIENT</div>

    <div class="client-info">
        <div>Client : <span>{{ $client->nom }}</span></div>
        <div>Contact : {{ $client->contact }}</div>
        <div>Adresse : {{ $client->address }}</div>
        <div>Date d'édition : {{ date('d/m/Y H:i') }}</div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 12%;">DATE</th>
                <th style="width: 15%;">TYPE</th>
                <th style="width: 15%;">RÉFÉRENCE</th>
                <th style="width: 28%;">DESCRIPTION</th>
                <th style="width: 10%;">DÉBIT (+)</th>
                <th style="width: 10%;">CRÉDIT (-)</th>
                <th style="width: 10%;">SOLDE</th>
            </tr>
        </thead>
        <tbody>
            @php $runningBalance = 0; @endphp
            @foreach($history as $item)
                @php $runningBalance += ($item['debit'] - $item['credit']); @endphp
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $item['type'] }}</td>
                    <td class="text-center">{{ $item['reference'] }}</td>
                    <td>{{ $item['description'] }}</td>
                    <td class="text-right">{{ $item['debit'] > 0 ? number_format($item['debit'], 0, ',', ' ') : '-' }}</td>
                    <td class="text-right">{{ $item['credit'] > 0 ? number_format($item['credit'], 0, ',', ' ') : '-' }}</td>
                    <td class="text-right font-bold {{ $runningBalance < 0 ? 'negative' : 'positive' }}">
                        {{ number_format($runningBalance, 0, ',', ' ') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="font-bold">Total Débit :</td>
                <td class="text-right">{{ number_format($history->sum('debit'), 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="font-bold">Total Crédit :</td>
                <td class="text-right">{{ number_format($history->sum('credit'), 0, ',', ' ') }}</td>
            </tr>
            <tr class="balance-row">
                <td class="font-bold">SOLDE FINAL :</td>
                <td class="text-right font-bold {{ $client->balance < 0 ? 'negative' : 'positive' }}">
                    {{ number_format($client->balance, 0, ',', ' ') }} FCFA
                </td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <div style="margin-top: 50px; font-style: italic; font-size: 8px;">
        * Un solde négatif (rouge) signifie que l'entreprise doit au client.<br>
        * Un solde positif (vert) signifie que le client doit à l'entreprise.
    </div>
</body>
</html>
