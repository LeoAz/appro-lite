<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture Dépôt {{ $invoice->number }}</title>
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
        .invoice-title {
            color: #1e40af;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .invoice-title span {
            color: #333;
            font-weight: normal;
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
        }
        .items-table td {
            padding: 8px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        .description-cell {
            text-align: left;
            width: 55%;
        }
        .quantity-cell {
            text-align: right;
            width: 20%;
        }
        .price-cell {
            text-align: center;
            width: 10%;
        }
        .total-cell {
            text-align: right;
            width: 15%;
        }
        .total-container {
            width: 100%;
            margin-top: 20px;
        }
        .total-box {
            float: right;
            width: 300px;
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 4px;
        }
        .total-row {
            width: 100%;
            margin-bottom: 5px;
        }
        .total-row.final {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
        }
        .total-label {
            display: inline-block;
            width: 60%;
            color: #666;
        }
        .total-value {
            display: inline-block;
            width: 35%;
            text-align: right;
        }
        .footer {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .signature-section {
            margin-top: 100px;
            width: 100%;
        }
        .signature-box {
            width: 30%;
            float: left;
            text-align: center;
        }
        .signature-box.right {
            float: right;
        }
        .signature-label {
            font-weight: bold;
            margin-bottom: 60px;
            text-decoration: underline;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%">
            <tr>
                <td style="vertical-align: top;">
                    <div style="font-weight: bold; font-size: 18px; color: #1e40af;">CORRIDOR PETROLEUM</div>
                    <div style="font-size: 10px; color: #666;">Solutions de gestion de carburant</div>
                </td>
                <td class="issuer-info">
                    <div class="issuer-name">{{ $invoice->issuer_name }}</div>
                    <div>RCCM: RB/COT/15 B 14120 - IFU: 3201510255106</div>
                    <div>Tél: (+229) 97 00 00 00 / 95 00 00 00</div>
                    <div>Email: contact@corridor-petroleum.com</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <div class="invoice-title">FACTURE DE VENTE SUR DÉPÔT : <span>#{{ $invoice->number }}</span></div>

    <div class="date-info">Date de facturation : <span>{{ $invoice->date->format('d/m/Y') }}</span></div>

    <table class="billing-info">
        <tr>
            <td>
                <div class="billing-label">Client</div>
                <div class="billing-value">{{ $invoice->client->nom }}</div>
                <div class="billing-address">
                    ID Client: #{{ $invoice->client->id }}<br>
                    Téléphone: {{ $invoice->client->telephone }}
                </div>
            </td>
            <td>
                <div class="billing-label">Dépôt</div>
                <div class="billing-value">{{ $invoice->depot->name }}</div>
                <div class="billing-address">
                    ID Dépôt: #{{ $invoice->depot->id }}
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Désignation / Compartiment</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td class="description-cell">
                        <strong>{{ $item->compartment->product }}</strong><br>
                        <span style="font-size: 9px; color: #666;">Compartiment #{{ $item->compartment->id }}</span>
                    </td>
                    <td class="quantity-cell">{{ number_format($item->quantity, 0, '.', ' ') }} L</td>
                    <td class="price-cell">{{ number_format($item->unit_price, 0, '.', ' ') }}</td>
                    <td class="total-cell">{{ number_format($item->total, 0, '.', ' ') }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-container clearfix">
        <div class="total-box">
            <div class="total-row final">
                <span class="total-label">MONTANT TOTAL</span>
                <span class="total-value">{{ number_format($invoice->total_amount, 0, '.', ' ') }} FCFA</span>
            </div>
        </div>
    </div>

    <div style="margin-top: 40px;">
        <p>Arrêté la présente facture à la somme de : <strong style="text-transform: uppercase;">{{ NumberToWords\NumberToWords::transformNumber('fr', $invoice->total_amount) }} FRANCS CFA</strong></p>
    </div>

    <div class="signature-section clearfix">
        <div class="signature-box">
            <div class="signature-label">Le Client</div>
        </div>
        <div class="signature-box right">
            <div class="signature-label">La Direction</div>
        </div>
    </div>

    <div class="footer">
        CORRIDOR PETROLEUM - Siège social: Cotonou, Bénin - SARL au capital de 1.000.000 FCFA<br>
        Facture générée le {{ date('d/m/Y à H:i') }} par Appro-Lite
    </div>
</body>
</html>
