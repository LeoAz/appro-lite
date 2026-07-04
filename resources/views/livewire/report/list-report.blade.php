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
                @foreach($cities as $city)
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model.live="selectedLocations" value="{{ $city->name }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">{{ $city->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="print-only hidden">
        @include('livewire.report.print-report', [
            'loads' => $this->getReportQuery()->get(),
            'type' => $type,
            'selectedLocations' => $selectedLocations,
            'selectedProduct' => $selectedProduct,
            'dateFrom' => $dateFrom,
            'dateUntil' => $dateUntil,
        ])
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
            @page {
                size: landscape;
            }
            .no-print, header, nav, .fi-sidebar, .fi-topbar, .fi-header-actions, .fi-ta-header-actions, .fi-ta-filters, .fi-ta-header-toolbar, .fi-ta-pagination, .stats-container, .fi-ta-content {
                display: none !important;
            }
            .fi-main {
                padding: 0 !important;
            }
            .print-only {
                display: block !important;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }
        }
    </style>
</div>
