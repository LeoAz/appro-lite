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
