<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach($this->statistics['count_by_product'] as $product => $count)
            <div class="bg-white p-4 shadow rounded-lg border-l-4 border-primary-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Camions {{ $product }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $count }}</p>
                    </div>
                    <div class="p-2 bg-primary-50 rounded-full text-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.25 2.25 0 0 0-1.813-.883H14.25M14.25 8.625c0-1.036.84-1.875 1.875-1.875h.375a3.75 3.75 0 0 1 3.75 3.75v3.75m-1.875-10.125a3.375 3.375 0 0 1 3.375 3.375M9 8.125c0-1.036.84-1.875 1.875-1.875h.375A3.75 3.75 0 0 1 15 10v.25m-3-4.125a3.375 3.375 0 0 1 3.375 3.375M9 15c0 1.036.84 1.875 1.875 1.875h.375A3.75 3.75 0 0 0 15 13.125V12.75m-3 4.125a3.375 3.375 0 0 0 3.375-3.375M9 15V8.125m0 6.875h1.5" />
                        </svg>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="bg-white p-4 shadow rounded-lg border-l-4 border-success-600">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Litres</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($this->statistics['total_litres'], 0, ',', ' ') }} L</p>
                </div>
                <div class="p-2 bg-success-50 rounded-full text-success-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52V4.5a3 3 0 0 0-3-3h-4.5a3 3 0 0 0-3 3v.47m0 0c-1.01.143-2.01.317-3 .52m3-.52V18c0 .357-.021.71-.062 1.057m1.522-4.11a48.448 48.448 0 0 0-3.328-1.545M12 10.5a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9Z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4 p-4 bg-white shadow rounded-lg no-print">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Produit</label>
                <select wire:model.live="selectedProduct" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Tous les produits</option>
                    <option value="FUEL">FUEL</option>
                    <option value="SUPER">SUPER</option>
                    <option value="GASOIL">GASOIL</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Du</label>
                <input type="date" wire:model.live="dateFrom" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Au</label>
                <input type="date" wire:model.live="dateUntil" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Villes (Lieu de {{ $status === 'EN COURS' ? 'chargement' : 'livraison' }})</label>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-2">
                @foreach(\App\Models\City::all() as $city)
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model.live="selectedLocations" value="{{ $city->name }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">{{ $city->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <button type="button" class="mb-3 inline-flex items-center gap-x-1.5 rounded-md bg-danger-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600"
            wire:click="printLoads"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="-ml-0.5 h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
        </svg>
        Imprimer les chargements
    </button>

    <div class="print-only hidden">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px;">
            <div style="width: 30%;">
                <div style="font-weight: bold; font-size: 14px;">APPRO-LITE</div>
                <div style="font-size: 10px;">Gestion des transports</div>
            </div>
            <div style="width: 40%; text-align: center;">
                <h1 style="margin: 0; font-size: 18px;">LISTE DES {{ $status === 'EN COURS' ? 'CHARGEMENTS' : 'LIVRAISONS' }}</h1>
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
    </div>

    {{ $this->table }}
    <style>
        @media print {
            @page {
                margin: 2cm;
            }
            body {
                padding: 1cm;
            }
            .no-print, header, nav, .fi-sidebar, .fi-topbar, .fi-header-actions, .fi-ta-header-actions, .fi-ta-filters, .fi-ta-header-toolbar, .fi-ta-pagination {
                display: none !important;
            }
            .fi-main {
                padding: 0 !important;
            }
            .print-only {
                display: block !important;
            }
            .fi-ta-content {
                border: none !important;
                box-shadow: none !important;
            }
            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            th, td {
                border: 1px solid #333 !important;
                padding: 4px !important;
                font-size: 10px !important;
            }
            .fi-ta-summaries-row {
                background-color: #f3f4f6 !important;
                font-weight: bold !important;
            }
        }
    </style>
</div>
