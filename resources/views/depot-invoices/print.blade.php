<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->number }}</title>
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
            width: 15%;
        }
        .price-cell {
            text-align: right;
            width: 15%;
        }
        .total-cell {
            text-align: right;
            width: 15%;
        }
        .totals-section {
            width: 100%;
            margin-top: 20px;
        }
        .totals-table {
            float: right;
            width: 300px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px;
            border: 1px solid #333;
        }
        .total-label {
            font-weight: bold;
            background-color: #f3f4f6;
            text-align: left;
        }
        .total-amount {
            text-align: right;
            font-weight: bold;
            color: #1e40af;
            font-size: 14px;
        }
        .footer {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
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
                <td>
                    <div style="color: #1e40af; font-size: 24px; font-weight: bold; letter-spacing: -1px;">
                        {{ $invoice->issuer_name }}
                    </div>
                </td>
                <td class="issuer-info">
                    <div class="issuer-name">{{ $invoice->issuer_name }}</div>
                    <div>Transport & Logistique</div>
                    <div>Lubumbashi, Haut-Katanga</div>
                    <div>République Démocratique du Congo</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <div class="invoice-title">FACTURE SUR DÉPÔT : <span>#{{ $invoice->number }}</span></div>

    <div class="date-info">Date de facturation : <span>{{ $invoice->date->format('d/m/Y') }}</span></div>

    <table class="billing-info">
        <tr>
            <td>
                <div class="billing-label">Facturer à</div>
                <div class="billing-value">{{ $invoice->client->nom }}</div>
            </td>
            <td>
                <div class="billing-label">Lieu de chargement</div>
                <div class="billing-value">{{ $invoice->depot->name }}</div>
                <div class="billing-label" style="margin-top: 10px;">Produit</div>
                <div class="billing-value">{{ $invoice->product }}</div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description / Compartiment</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td class="description-cell">
                        Compartiment: {{ $item->compartment->product }}
                    </td>
                    <td class="quantity-cell">
                        {{ number_format($item->quantity, 0, '.', ' ') }} L
                    </td>
                    <td class="price-cell">
                        {{ number_format($item->unit_price, 0, '.', ' ') }}
                    </td>
                    <td class="total-cell">
                        {{ number_format($item->total, 0, '.', ' ') }} FCFA
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="total-label">MONTANT TOTAL</td>
                <td class="total-amount">{{ number_format($invoice->total_amount, 0, '.', ' ') }} FCFA</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <div style="margin-top: 50px;">
        <div style="font-weight: bold; margin-bottom: 10px; text-decoration: underline;">Notes:</div>
        <p style="font-size: 10px;">La quantité facturée a été directement déduite du stock du dépôt {{ $invoice->depot->name }}.</p>
    </div>

    <div class="footer">
        {{ $invoice->issuer_name }} - S.A.R.L au capital de 10.000.000 FCFA - RCCM: CD/LSH/RCCM/22-B-01234 - ID Nat: 01-123-N123456 - NIF: A1234567Z
    </div>
</body>
</html>
