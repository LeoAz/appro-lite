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

        @media print {
            @page {
                size: landscape;
                margin: 2cm;
            }
            body {
                margin: 0;
                padding: 1cm;
            }
            .no-print {
                display: none !important;
            }
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
            <div style="font-size: 10px;">Date d'édition: {{ now()->addHours(1)->format('d/m/Y H:i') }}</div>
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
                <th>N°</th>
                @if($type === 'livraison')
                    <th>Date Liv.</th>
                @endif
                <th>Date</th>
                <th>Lieu</th>
                <th>Produit</th>
                <th>Litres</th>
                <th>Véhicule</th>
                @if($type === 'livraison')
                    <th>Lieu Liv.</th>
                    <th>Client</th>
                @endif
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalVolume = 0;
                $lastClient = null;
                $lastDate = null;
                $dateField = $type === 'chargement' ? 'load_date' : 'unload_date';
            @endphp
            @foreach ($loads as $index => $load)
                @php $totalVolume += (float) ($load->volume ?? 0); @endphp

                @php
                    $currentClient = $load->client?->nom ?? $load->client_name ?? 'Sans Client';
                    $currentDate = $load->$dateField ? $load->$dateField->format('d/m/Y') : 'Sans Date';
                @endphp

                @if($type === 'livraison' && ($lastDate === null || $lastDate !== $currentDate))
                    <tr style="background-color: #f3f4f6; font-style: italic;">
                        <td colspan="{{ $type === 'livraison' ? 10 : 7 }}">Date : {{ $currentDate }}</td>
                    </tr>
                    @php
                        $lastDate = $currentDate;
                        $lastClient = null; // Reset client when date changes
                    @endphp
                @endif

                @if($type === 'livraison' && ($lastClient === null || $lastClient !== $currentClient))
                    <tr style="background-color: #e5e7eb; font-weight: bold;">
                        <td colspan="{{ $type === 'livraison' ? 10 : 7 }}" style="padding-left: 20px;">Client : {{ $currentClient }}</td>
                    </tr>
                    @php $lastClient = $currentClient; @endphp
                @endif

                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if($type === 'livraison')
                        <td>{{ $load->unload_date ? $load->unload_date->format('d/m/Y') : '-' }}</td>
                    @endif
                    <td>{{ $load->load_date->format('d/m/Y') }}</td>
                    <td>{{ $load->load_location }}</td>
                    <td>{{ $load->product }}</td>
                    <td>{{ number_format((float) ($load->volume ?? 0), 0, ',', ' ') }}</td>
                    <td>{{ $load->vehicle_registration ?? '-' }}</td>
                    @if($type === 'livraison')
                        <td>{{ $load->unload_location ?? '-' }}</td>
                        <td>{{ $load->client?->nom ?? $load->client_name ?? '-' }}</td>
                    @endif
                    <td>{{ $load->status }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="{{ $type === 'livraison' ? 5 : 4 }}" class="text-right">TOTAL</th>
                <th>{{ number_format((float) ($totalVolume ?? 0), 0, ',', ' ') }}</th>
                <th colspan="{{ $type === 'livraison' ? 4 : 2 }}"></th>
            </tr>
        </tfoot>
    </table>

    @php
        $countByProduct = [];
        $litresByProduct = [];
        $countByClient = [];
        $totalTrucks = $loads->count();
        $totalLitres = 0;

        foreach ($loads as $load) {
            $product = $load->product ?? 'Inconnu';
            $client = $load->client?->nom ?? $load->client_name ?? 'Sans Client';
            $volume = (float) ($load->volume ?? 0);

            if (!isset($countByProduct[$product])) {
                $countByProduct[$product] = 0;
                $litresByProduct[$product] = 0;
            }

            if (!isset($countByClient[$client])) {
                $countByClient[$client] = 0;
            }

            $countByProduct[$product]++;
            $litresByProduct[$product] += $volume;
            $countByClient[$client]++;
            $totalLitres += $volume;
        }
    @endphp

    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <div style="flex: 1; border: 1px solid #333; padding: 10px;">
            <h2 style="font-size: 12px; margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 5px;">Nombre de camions par client</h2>
            <table style="border: none; margin-bottom: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid #333;">
                        <th style="border: none; background: none; padding: 4px; font-size: 10px;">Client</th>
                        <th style="border: none; background: none; padding: 4px; font-size: 10px; text-align: right;">Camions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($countByClient as $client => $count)
                        <tr>
                            <td style="border: none; padding: 4px;">{{ $client }}</td>
                            <td style="border: none; padding: 4px; text-align: right;">{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; border-top: 1px solid #333;">
                        <td style="border: none; padding: 4px;">TOTAL</td>
                        <td style="border: none; padding: 4px; text-align: right;">{{ $totalTrucks }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="flex: 1; border: 1px solid #333; padding: 10px;">
            <h2 style="font-size: 12px; margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 5px;">Nombre de camions par produit</h2>
            <table style="border: none; margin-bottom: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid #333;">
                        <th style="border: none; background: none; padding: 4px; font-size: 10px;">Produit</th>
                        <th style="border: none; background: none; padding: 4px; font-size: 10px; text-align: right;">Camions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($countByProduct as $product => $count)
                        <tr>
                            <td style="border: none; padding: 4px;">{{ $product }}</td>
                            <td style="border: none; padding: 4px; text-align: right;">{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; border-top: 1px solid #333;">
                        <td style="border: none; padding: 4px;">TOTAL</td>
                        <td style="border: none; padding: 4px; text-align: right;">{{ $totalTrucks }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="flex: 1; border: 1px solid #333; padding: 10px;">
            <h2 style="font-size: 12px; margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 5px;">Nombre de litres par produit</h2>
            <table style="border: none; margin-bottom: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid #333;">
                        <th style="border: none; background: none; padding: 4px; font-size: 10px;">Produit</th>
                        <th style="border: none; background: none; padding: 4px; font-size: 10px; text-align: right;">Litres</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($litresByProduct as $product => $litres)
                        <tr>
                            <td style="border: none; padding: 4px;">{{ $product }}</td>
                            <td style="border: none; padding: 4px; text-align: right;">{{ number_format((float) ($litres ?? 0), 0, ',', ' ') }} L</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; border-top: 1px solid #333;">
                        <td style="border: none; padding: 4px;">TOTAL GÉNÉRAL</td>
                        <td style="border: none; padding: 4px; text-align: right;">{{ number_format((float) ($totalLitres ?? 0), 0, ',', ' ') }} L</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>
</html>
