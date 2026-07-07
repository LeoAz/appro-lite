<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
        }
        .info {
            margin-bottom: 30px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            vertical-align: top;
            width: 50%;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #d1d5db;
        }
        .items td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals {
            width: 100%;
        }
        .totals table {
            float: right;
            width: 40%;
        }
        .totals td {
            padding: 5px 10px;
        }
        .total-row {
            font-weight: bold;
            font-size: 16px;
            color: #1e40af;
            border-top: 2px solid #3b82f6;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">FACTURE</div>
                    <div class="label">N° {{ $invoice->number }}</div>
                </td>
                <td style="text-align: right;">
                    <strong>Appro-Lite</strong><br>
                    Date de facturation : {{ $invoice->date->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>
                    <div class="label">Émetteur :</div>
                    <strong>Appro-Lite</strong><br>
                    Service Facturation
                </td>
                <td>
                    <div class="label">Client :</div>
                    <strong>{{ $invoice->client->nom }}</strong><br>
                    {{ $invoice->client->address }}<br>
                    {{ $invoice->client->contact }}
                </td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
        <tr>
            <th>Livraison #</th>
            <th>Date</th>
            <th>Qte Livrée</th>
            <th>Prix Unit.</th>
            <th>Manquant</th>
            <th style="text-align: right;">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->delivery->id }}</td>
                <td>{{ $item->delivery->unload_date->format('d/m/Y') }}</td>
                <td>{{ number_format($item->quantity_delivered, 0, ',', ' ') }} L</td>
                <td>{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                <td>{{ number_format($item->missing_quantity, 0, ',', ' ') }} L</td>
                <td style="text-align: right;">{{ number_format($item->total, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Total Manquant :</td>
                <td style="text-align: right;"><strong>{{ number_format($invoice->total_missing, 0, ',', ' ') }} L</strong></td>
            </tr>
            <tr class="total-row">
                <td>MONTANT TOTAL :</td>
                <td style="text-align: right;">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        Merci de votre confiance.<br>
        Facture générée le {{ now()->format('d/m/Y H:i') }}
    </div>
</div>
</body>
</html>
