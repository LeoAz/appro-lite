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
        .manquant-text {
            font-size: 9px;
            color: #333;
            display: block;
            margin-top: 3px;
        }
        .net-text {
            font-size: 9px;
            color: #666;
            display: block;
            text-align: right;
        }
        .totals-section {
            width: 100%;
        }
        .totals-table {
            float: right;
            width: 40%;
        }
        .totals-table td {
            padding: 5px;
        }
        .total-amount-label {
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }
        .total-amount-value {
            text-align: right;
            font-weight: bold;
            font-size: 14px;
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
                    <div>Tél : +223 20 22 00 00 / +223 70 00 00 00</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <div class="invoice-title">Facture N° : <span>{{ $invoice->number }}</span></div>

    <div class="date-info">Date : <span>{{ $invoice->date->format('d/m/Y') }}</span></div>

    <table class="billing-info">
        <tr>
            <td>
                <div class="billing-label">DE</div>
                <div class="billing-value">CORRIDOR PETROLEUM</div>
                <div class="billing-address">
                    BAMAKO, MALI<br>
                    Tél: +223 20 22 00 00<br>
                    Email: contact@corridor-petroleum.com
                </div>
            </td>
            <td>
                <div class="billing-label">À</div>
                <div class="billing-value">{{ $invoice->client_name }}</div>
                <div class="billing-address">
                    BAMAKO<br>
                    @if(isset($invoice->client) && $invoice->client->contact)
                        Contact: {{ $invoice->client->contact }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="description-cell">DESCRIPTION</th>
                <th class="quantity-cell">QUANTITÉ (L)</th>
                <th class="price-cell">PU</th>
                <th class="total-cell">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                @php
                    $load = $item->delivery;
                    $product = $load->product ?? 'GASOIL';
                    $vehicle = $load->vehicle_registration ?? 'N/A';
                    $trailer = $load->trailer_registration ?? '';
                    $desc = $product . ' — ' . $vehicle . ($trailer ? ' / ' . $trailer : '');
                @endphp
                <tr>
                    <td class="description-cell">
                        {{ $desc }}
                        <span class="manquant-text">(Manquant: {{ number_format($item->missing_quantity, 0, ',', ' ') }} L)</span>
                        @if($item->bl_number)
                            <span class="manquant-text">N° BL: {{ $item->bl_number }}</span>
                        @endif
                    </td>
                    <td class="quantity-cell">
                        <strong>{{ number_format($load->volume ?? 0, 0, ',', ' ') }}</strong>
                        <span class="net-text">Net: {{ number_format($item->quantity_delivered, 0, ',', ' ') }}</span>
                    </td>
                    <td class="price-cell">
                        {{ number_format($item->unit_price, 0, ',', ' ') }}
                    </td>
                    <td class="total-cell">
                        <strong>{{ number_format($item->total, 0, ',', ' ') }}</strong>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="total-amount-label">Montant Total:</td>
                <td class="total-amount-value">{{ number_format($invoice->total_amount, 0, ',', ' ') }}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
