<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des chargements</title>
    <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 1cm;
            }

            @page {
                size: landscape;
                margin: 2cm;
            }

            h1 {
                text-align: center;
                font-size: 16px;
                margin-bottom: 20px;
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
                font-size: 12px; /* Taille de police pour l'en-tête */
                text-align: left;
            }

            td {
                font-size: 10px; /* Taille de police pour le corps */
            }

            /* Alternance des couleurs pour les lignes */
            tbody tr:nth-child(even) {
                background-color: #f9f9f9;
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
            <h1 style="margin: 0; font-size: 18px;">LISTE DES {{ $status === 'EN COURS' ? 'CHARGEMENTS' : 'LIVRAISONS' }}</h1>
            <div style="font-size: 10px;">Date d'édition: {{ now()->format('d/m/Y') }}</div>
        </div>
        <div style="width: 30%; text-align: right;">
            <div style="font-size: 10px;">Document Officiel</div>
            @php
                $qrData = "APPRO-LITE - LISTE DES " . ($status === 'EN COURS' ? 'CHARGEMENTS' : 'LIVRAISONS') . "\n" .
                          "Date: " . now()->format('d/m/Y') . "\n" .
                          "Total Volume: " . number_format($loads->sum('volume'), 0, ',', ' ') . " L";
            @endphp
            <div style="margin-top: 5px;">
                <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(60)->generate($qrData)) !!} ">
            </div>
        </div>
    </div>

    @if(($dateFrom ?? false) || ($dateUntil ?? false) || ($selectedProduct ?? false) || !empty($selectedLocations ?? []))
        <div style="margin-bottom: 20px; font-size: 10px; border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9;">
            <strong>Filtres appliqués :</strong>
            @if($dateFrom ?? false) Du: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} @endif
            @if($dateUntil ?? false) Au: {{ \Carbon\Carbon::parse($dateUntil)->format('d/m/Y') }} @endif
            @if($selectedProduct ?? false) | Produit: {{ $selectedProduct }} @endif
            @if(!empty($selectedLocations ?? [])) | Villes: {{ implode(', ', $selectedLocations) }} @endif
        </div>
    @endif
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Date</th>
                <th>Lieu</th>
                <th>Produit</th>
                <th>Litres</th>
                <th>Véhicule</th>
                @if($status === 'LIVRÉ')
                    <th>Date Liv.</th>
                    <th>Lieu Liv.</th>
                    <th>Client</th>
                @endif
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @php $totalVolume = 0; @endphp
            @foreach ($loads as $index => $load)
                @php $totalVolume += (float) ($load->volume ?? 0); @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $load->load_date->format('d/m/Y') }}</td>
                    <td>{{ $load->load_location }}</td>
                    <td>{{ $load->product }}</td>
                    <td>{{ number_format((float) ($load->volume ?? 0), 0, ',', ' ') }}</td>
                    <td>{{ $load->vehicle_registration ?? '-' }}</td>
                    @if($status === 'LIVRÉ')
                        <td>{{ $load->unload_date?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $load->unload_location ?? '-' }}</td>
                        <td>{{ $load->client_name ?? '-' }}</td>
                    @endif
                    <td>{{ $load->status }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="4" style="text-align: right;">TOTAL</td>
                <td>{{ number_format((float) ($totalVolume ?? 0), 0, ',', ' ') }}</td>
                <td colspan="{{ $status === 'LIVRÉ' ? 5 : 2 }}"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
