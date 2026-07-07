<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de Stock</title>
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
        .report-title {
            color: #1e40af;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #1e40af;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .data-table th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 8px;
            border: 1px solid #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .data-table td {
            padding: 8px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        .text-right {
            text-align: right;
        }
        .section-title {
            font-weight: bold;
            font-size: 12px;
            color: #1e40af;
            margin-bottom: 10px;
            border-left: 4px solid #1e40af;
            padding-left: 8px;
        }
        .footer {
            position: fixed;
            bottom: 40px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 9px;
            color: #999;
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

    <div class="report-title">Rapport de Suivi de Stock</div>

    <div class="info-section">
        <div class="info-item">
            <span class="info-label">Dépôt :</span>
            {{ $selectedDepot->name ?? 'Tous les dépôts' }}
        </div>
        @if(isset($selectedProduct) && $selectedProduct)
            <div class="info-item">
                <span class="info-label">Produit :</span>
                {{ $selectedProduct }}
            </div>
        @endif
        <div class="info-item">
            <span class="info-label">Date d'édition :</span>
            {{ $date->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="section-title">État des Stocks (Compartiments)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Dépôt</th>
                <th>Produit</th>
                <th class="text-right">Stock Actuel (L)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($compartments as $compartment)
                <tr>
                    <td>{{ $compartment->depot->name ?? 'N/A' }}</td>
                    <td>{{ $compartment->product }}</td>
                    <td class="text-right"><strong>{{ number_format((float) ($compartment->quantity ?? 0), 0, ',', ' ') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Aucun stock trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(isset($purchases) && count($purchases) > 0)
        <div class="section-title">Historique des Achats (Mouvements Entrants)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Produit</th>
                    <th class="text-right">Quantité</th>
                    <th class="text-right">P.U</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->purchase_date?->format('d/m/Y') }}</td>
                        <td>{{ $purchase->product }}</td>
                        <td class="text-right" style="color: #1e40af;">+{{ number_format((float) ($purchase->quantity ?? 0), 0, ',', ' ') }} L</td>
                        <td class="text-right">{{ number_format((float) $purchase->unit_price, 0, ',', ' ') }}</td>
                        <td class="text-right"><strong>{{ number_format((float) $purchase->total_price, 0, ',', ' ') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(isset($loads) && count($loads) > 0)
        <div class="section-title">Historique des Chargements (Mouvements Sortants)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Camion</th>
                    <th>Produit</th>
                    <th class="text-right">Volume</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($loads as $load)
                    <tr>
                        <td>{{ $load->load_date->format('d/m/Y') }}</td>
                        <td>{{ $load->vehicle_registration }}</td>
                        <td>{{ $load->product }}</td>
                        <td class="text-right" style="color: #dc2626;">-{{ number_format((float) ($load->volume ?? 0), 0, ',', ' ') }} L</td>
                        <td>{{ $load->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        CORRIDOR PETROLEUM - Rapport généré le {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
