<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des {{ $type }}s</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
            font-size: 12px;
            text-align: left;
        }

        td {
            font-size: 10px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px;">
        <div style="width: 30%;">
            <div style="font-weight: bold; font-size: 14px;">APPRO-LITE</div>
            <div style="font-size: 10px;">Gestion des transports</div>
        </div>
        <div style="width: 40%; text-align: center;">
            <h1 style="margin: 0; font-size: 18px;">RAPPORT DES {{ $type }}S</h1>
            <div style="font-size: 10px;">Date d'édition: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div style="width: 30%; text-align: right;">
            <div style="font-size: 10px;">Document Officiel</div>
        </div>
    </div>

    @if($dateFrom || $dateUntil || $selectedProduct || !empty($selectedLocations))
        <div style="margin-bottom: 10px; font-size: 10px;">
            <strong>Filtres appliqués :</strong>
            @if($dateFrom) Du: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} @endif
            @if($dateUntil) Au: {{ \Carbon\Carbon::parse($dateUntil)->format('d/m/Y') }} @endif
            @if($selectedProduct) | Produit: {{ $selectedProduct }} @endif
            @if(!empty($selectedLocations)) | Villes: {{ implode(', ', $selectedLocations) }} @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Lieu</th>
                <th>Produit</th>
                <th>Litres</th>
                <th>Véhicule</th>
                @if($type === 'livraison')
                    <th>Date Liv.</th>
                    <th>Lieu Liv.</th>
                    <th>Client</th>
                @endif
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @php $totalCapacity = 0; @endphp
            @foreach ($loads as $load)
                @php $totalCapacity += $load->capacity; @endphp
                <tr>
                    <td>{{ $load->load_date->format('d/m/Y') }}</td>
                    <td>{{ $load->load_location }}</td>
                    <td>{{ $load->product }}</td>
                    <td>{{ number_format($load->capacity, 0, ',', ' ') }}</td>
                    <td>{{ $load->vehicle_registration ?? '-' }}</td>
                    @if($type === 'livraison')
                        <td>{{ $load->unload_date ? $load->unload_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $load->unload_location ?? '-' }}</td>
                        <td>{{ $load->client_name ?? '-' }}</td>
                    @endif
                    <td>{{ $load->status }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="{{ $type === 'livraison' ? 3 : 3 }}" class="text-right">TOTAL</th>
                <th>{{ number_format($totalCapacity, 0, ',', ' ') }}</th>
                <th colspan="{{ $type === 'livraison' ? 5 : 2 }}"></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
