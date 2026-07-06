<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Achats de Carburant</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        @page { margin: 2cm; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Liste des Achats de Carburant</h1>
        <p>Généré le : {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Dépôt</th>
                <th>Produit</th>
                <th>Quantité</th>
                <th>P.U.</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php $totalQty = 0; $totalPrice = 0; @endphp
            @foreach ($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                    <td>{{ $purchase->depot->name }}</td>
                    <td>{{ $purchase->product }}</td>
                    <td>{{ number_format((float) ($purchase->quantity ?? 0), 2) }} L</td>
                    <td>{{ number_format((float) ($purchase->unit_price ?? 0), 0, ',', ' ') }} F</td>
                    <td>{{ number_format((float) ($purchase->total_price ?? 0), 0, ',', ' ') }} F</td>
                </tr>
                @php
                    $totalQty += $purchase->quantity;
                    $totalPrice += $purchase->total_price;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">TOTAUX</td>
                <td>{{ number_format((float) ($totalQty ?? 0), 2) }} L</td>
                <td>-</td>
                <td>{{ number_format((float) ($totalPrice ?? 0), 0, ',', ' ') }} F</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Page 1
    </div>
</body>
</html>
