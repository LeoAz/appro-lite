<?php

namespace App\Livewire\Report;

use App\Models\City;
use App\Models\Load;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $type;

    // Filtres personnalisés
    public $selectedLocations = [];
    public $selectedProduct = '';
    public $dateFrom = '';
    public $dateUntil = '';

    public function applyFilters()
    {
        $this->resetTable();
    }

    public $cities;

    public function mount($type = 'chargement')
    {
        $this->type = $type;
        $this->cities = \App\Models\City::all();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedLocations', 'selectedProduct', 'dateFrom', 'dateUntil'])) {
            $this->resetTable();
        }
    }

    public function getReportQuery()
    {
        $query = Load::query();

        if ($this->type === 'chargement') {
            $query->where('status', 'CHARGÉ');
        } else {
            $query->where('status', 'LIVRÉ');
        }

        // Application des filtres personnalisés
        $locationField = $this->type === 'chargement' ? 'load_location' : 'unload_location';
        $dateField = $this->type === 'chargement' ? 'load_date' : 'unload_date';

        if (!empty($this->selectedLocations)) {
            $query->whereIn($locationField, $this->selectedLocations);
        }

        if ($this->selectedProduct) {
            $query->where('product', $this->selectedProduct);
        }

        if ($this->dateFrom) {
            $query->whereDate($dateField, '>=', $this->dateFrom);
        }

        if ($this->dateUntil) {
            $query->whereDate($dateField, '<=', $this->dateUntil);
        }

        return $query->orderBy($dateField, 'asc')
            ->orderBy('client_name', 'asc');
    }

    public function table(Table $table): Table
    {
        $dateField = $this->type === 'chargement' ? 'load_date' : 'unload_date';

        return $table
            ->query($this->getReportQuery())
            ->defaultSort($dateField, "asc")
            ->groups([
                \Filament\Tables\Grouping\Group::make($dateField)
                    ->label('Date')
                    ->date()
                    ->collapsible(),
                \Filament\Tables\Grouping\Group::make('client_name')
                    ->label('Client')
                    ->collapsible(),
            ])
            ->defaultGroup($dateField)
            ->paginated(false)
            ->headerActions([
                Action::make('print')
                    ->label('Imprimer')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(fn () => $this->dispatch('print-report')),
                Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action('downloadPdf'),
            ])
            ->columns([
                TextColumn::make("index")
                    ->label("N°")
                    ->rowIndex(),
                TextColumn::make("load_date")
                    ->label("Date Chargement")
                    ->date("d-m-Y")
                    ->sortable(),
                TextColumn::make("load_location")
                    ->label("Lieu Chargement"),
                TextColumn::make("product")
                    ->label("Produit"),
                TextColumn::make("capacity")
                    ->label("Litres")
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' ')
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total')->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' ')),
                TextColumn::make("vehicle_registration")
                    ->label("Véhicule"),
                TextColumn::make("status")
                    ->label("Statut")
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'EN COURS' => 'info',
                        'LIVRÉ' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make("unload_date")
                    ->label("Date Livraison")
                    ->date("d-m-Y")
                    ->toggleable()
                    ->hidden(fn() => $this->type === 'chargement')
                    ->searchable(),
                TextColumn::make("unload_location")
                    ->label("Lieu Livraison")
                    ->toggleable()
                    ->hidden(fn() => $this->type === 'chargement')
                    ->searchable(),
                TextColumn::make("client_name")
                    ->label("Client")
                    ->toggleable()
                    ->hidden(fn() => $this->type === 'chargement')
                    ->searchable(),
            ]);
    }

    public function downloadPdf()
    {
        $loads = $this->getReportQuery()->get();

        $pdf = Pdf::loadView('livewire.report.print-report', [
            'loads' => $loads,
            'type' => $this->type,
            'selectedLocations' => $this->selectedLocations,
            'selectedProduct' => $this->selectedProduct,
            'dateFrom' => $this->dateFrom,
            'dateUntil' => $this->dateUntil,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print $pdf->output(),
            "Rapport_" . $this->type . "_" . now()->format('Y-m-d') . ".pdf"
        );
    }

    public function getStatisticsProperty()
    {
        $loads = $this->getReportQuery()->get();

        $stats = [
            'count_by_product' => [],
            'litres_by_product' => [],
            'count_by_client' => [],
            'total_litres' => 0,
            'total_trucks' => $loads->count(),
        ];

        foreach ($loads as $load) {
            $product = $load->product ?? 'Inconnu';
            $client = $load->client_name ?? 'Sans Client';
            $capacity = (int) $load->capacity;

            if (!isset($stats['count_by_product'][$product])) {
                $stats['count_by_product'][$product] = 0;
                $stats['litres_by_product'][$product] = 0;
            }

            if (!isset($stats['count_by_client'][$client])) {
                $stats['count_by_client'][$client] = 0;
            }

            $stats['count_by_product'][$product]++;
            $stats['litres_by_product'][$product] += $capacity;
            $stats['count_by_client'][$client]++;
            $stats['total_litres'] += $capacity;
        }

        return $stats;
    }

    public function render()
    {
        return view('livewire.report.list-report');
    }
}
