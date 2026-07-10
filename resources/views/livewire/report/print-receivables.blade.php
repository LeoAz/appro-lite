<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>État des Créances</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #1e40af; text-transform: uppercase; }
        .info { margin-bottom: 15px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f3f4f6; padding: 6px; border: 1px solid #333; text-align: left; text-transform: uppercase; font-size: 10px; }
        td { padding: 6px; border: 1px solid #333; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">État des Créances Client</div>
        <div>CORRIDOR PETROLEUM</div>
    </div>

    <div class="info">
        Date d'édition : {{ $date->format('d/m/Y H:i') }}<br>
        Client : {{ $client->nom ?? 'Tous les clients' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date Fact.</th>
                <th>N° Facture</th>
                @if(!$client)
                    <th>Client</th>
                @endif
                <th>Véhicule</th>
                <th>Produit</th>
                <th class="text-right">Qté Facturée</th>
                <th class="text-right">Montant Dû</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->invoice->date->format('d/m/Y') }}</td>
                    <td>{{ $item->invoice->number }}</td>
                    @if(!$client)
                        <td>{{ $item->invoice->client->nom ?? 'N/A' }}</td>
                    @endif
                    <td>{{ $item->delivery->vehicle_registration ?? 'N/A' }}</td>
                    <td>{{ $item->delivery->product ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($item->quantity_delivered, 0, ',', ' ') }} L</td>
                    <td class="text-right">{{ number_format($item->total, 0, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold" style="background-color: #f3f4f6;">
                <td colspan="{{ $client ? 5 : 6 }}" class="text-right">TOTAL GÉNÉRAL DES CRÉANCES</td>
                <td class="text-right">{{ number_format($total_receivable, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Document généré le {{ $date->format('d/m/Y H:i') }} - Appro-Lite
    </div>
</body>
</html>
