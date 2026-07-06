<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Stock</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; }
        .status-Normal { color: green; }
        .status-Bas { color: orange; }
        .status-Critique { color: red; }
        @page { margin: 2cm; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport de Suivi de Stock</h1>
        @if(isset($selectedDepot) && $selectedDepot)
            <h2>Dépôt : {{ $selectedDepot->name }}</h2>
        @endif
        <p>Date : {{ $date->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Dépôt</th>
                <th>Produit</th>
                <th>Stock Actuel</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($compartments as $compartment)
                <tr>
                    <td>{{ $compartment->depot->name }}</td>
                    <td>{{ $compartment->product }}</td>
                    <td>{{ number_format($compartment->quantity, 2) }} L</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Généré le {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
