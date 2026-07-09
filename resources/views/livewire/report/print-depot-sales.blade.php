<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de Vente sur Dépôt</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; color: #1e40af; text-transform: uppercase; }
        .filters { margin-bottom: 20px; font-style: italic; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f3f4f6; padding: 8px; border: 1px solid #d1d5db; text-align: left; }
        td { padding: 8px; border: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Rapport de Vente sur Dépôt</div>
        <div>CORRIDOR PETROLEUM</div>
        <div style="margin-top: 5px;">Généré le {{ $date->format('d/m/Y H:i') }}</div>
    </div>

    <div class="filters">
        Période :
        @if($date_from && $date_to)
            du {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}
        @elseif($date_from)
            depuis le {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }}
        @elseif($date_to)
            jusqu'au {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}
        @else
            Toute la période
        @endif
        @if($client_id)
            | Client : {{ \App\Models\Client::find($client_id)?->nom }}
        @endif
        @if($depot_id)
            | Dépôt : {{ \App\Models\Depot::find($depot_id)?->name }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>N° Facture</th>
                <th>Client</th>
                <th>Dépôt</th>
                <th>Produit</th>
                <th class="text-right">Montant Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->date->format('d/m/Y') }}</td>
                    <td>{{ $invoice->number }}</td>
                    <td>{{ $invoice->client->nom }}</td>
                    <td>{{ $invoice->depot->name }}</td>
                    <td>{{ $invoice->product }}</td>
                    <td class="text-right">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold">
                <td colspan="5" class="text-right">TOTAL GÉNÉRAL</td>
                <td class="text-right">{{ number_format($total_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Document généré automatiquement par Appro-Lite
    </div>
</body>
</html>
