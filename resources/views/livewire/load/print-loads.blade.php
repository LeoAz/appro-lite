<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des chargement</title>
    <style>
            body {
                font-family: Arial, sans-serif;
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
    <h1>Liste des chargements</h1>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Ville</th>
                <th>Produit</th>
                <th>Litres</th>
                <th>Vehicule</th>
                <th>Transporteur</th>
                <th>Dépot</th>
                <th>Status</th>
                <th>Déchmt</th>
                <th>Lieu</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($loads as $load)
                <tr>
                    <td>{{ $load->load_date->format('d/m/Y') }}</td>
                    <td>{{ $load->city->name }}</td>
                    <td>{{ $load->product }}</td>
                    <td>{{ $load->capacity}}</td>
                    <td>{{ $load->vehicle->registration ?? '-' }}</td>
                    <td>{{ $load->vehicle->carrier->nom  ?? '-'}}</td>
                    <td>{{ $load->depot->name}}</td>
                    <td>{{ $load->status }}</td>
                    <td>{{ $load->unload_date?->format('d/m/Y')}}</td>
                    <td>{{ $load->unload_location ?? '-'}}</td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
