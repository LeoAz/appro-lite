<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 no-print">
        @foreach($this->statistics['count_by_product'] as $product => $count)
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
                <div class="p-3 bg-primary-50 rounded-lg">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 8v2m0 0v2m0-2h2"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ $product }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $count }} <span class="text-sm font-normal text-gray-500">camions</span></p>
                </div>
            </div>
        @endforeach

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-success-50 rounded-lg">
                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.288a2 2 0 01-1.108.209 4 4 0 00-3.478 2.013m6.246-10.125l-2.973-1.5c-.8-.403-1.605-.5-2.43-.508a3 3 0 00-3.034 2.813L7 9m0 0l2.124.708a2.25 2.25 0 001.42 0L12 9V6m0 0l2.124.708a2.25 2.25 0 001.42 0L17 9V6m-7 6h.008v.008H10V12zm3 0h.008v.008H13V12zm3 0h.008v.008H16V12z"></path>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Litres</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format((float)($this->statistics['total_litres'] ?? 0), 0, ',', ' ') }} <span class="text-sm font-normal text-gray-500">L</span></p>
            </div>
        </div>
    </div>

    <div class="mb-4 p-4 bg-white shadow rounded-lg no-print">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
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
            <div>
                <button wire:click="applyFilters" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Filtrer
                </button>
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
