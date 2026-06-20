<div>
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Villes (Lieu de {{ $type }})</label>
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

    <div class="print-only hidden">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px;">
            <div style="width: 30%;">
                <div style="font-weight: bold; font-size: 14px;">APPRO-LITE</div>
                <div style="font-size: 10px;">Gestion des transports</div>
            </div>
            <div style="width: 40%; text-align: center;">
                <h1 style="margin: 0; font-size: 18px;">RAPPORT DES {{ strtoupper($type) }}S</h1>
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

    @script
    <script>
        $wire.on('print-report', () => {
            window.print();
        });
    </script>
    @endscript

    <style>
        @media print {
            .no-print, header, nav, .fi-sidebar, .fi-topbar, .fi-header-actions, .fi-ta-filters, .fi-ta-header-toolbar, .fi-ta-pagination {
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
