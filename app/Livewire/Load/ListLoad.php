<?php

namespace App\Livewire\Load;

use App\Models\Load;
use App\Enums\LoadStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ListLoad extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $status;
    public $selectedLocations = [];
    public $selectedProduct = '';
    public $dateFrom = '';
    public $dateUntil = '';
    public $cities;

    public function applyFilters()
    {
        $this->resetTable();
    }

    public function mount($status)
    {
        $this->status = $status;
        $this->cities = \App\Models\City::all();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedLocations', 'selectedProduct', 'dateFrom', 'dateUntil'])) {
            $this->resetTable();
        }
    }

    public function getFilteredQuery()
    {
        $query = Load::query();

        if ($this->status === 'CHARGÉ') {
             $query->where("status", LoadStatus::Pending); // Note: Should probably check what CHARGÉ means exactly, but keeping current logic
        } elseif ($this->status === LoadStatus::Unloaded) {
             $query->whereIn("status", [LoadStatus::Unloaded, LoadStatus::Invoiced, LoadStatus::Paid]);
        } else {
             $query->where("status", $this->status);
        }

        // Application des filtres personnalisés
        $locationField = ($this->status === 'EN COURS' || $this->status === 'CHARGÉ') ? 'load_location' : 'unload_location';
        $dateField = ($this->status === 'EN COURS' || $this->status === 'CHARGÉ') ? 'load_date' : 'unload_date';

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

        return $query;
    }

    public function table(Table $table): Table
    {
        $defaultSortColumn = ($this->status === 'EN COURS' || $this->status === 'CHARGÉ') ? 'load_date' : 'unload_date';

        return $table
            ->query($this->getFilteredQuery())
            ->defaultSort($defaultSortColumn, "desc")
            ->paginated([10, 25, 50, 100])
            ->selectable()
            ->columns([
                TextColumn::make("index")
                    ->label("N°")
                    ->rowIndex(),
                TextColumn::make("unload_date")
                    ->label("Date Livraison")
                    ->date("d-m-Y")
                    ->toggleable()
                    ->hidden(fn() => $this->status === "EN COURS")
                    ->searchable(),
                TextColumn::make("load_location")
                    ->label("Lieu Chargement")
                    ->searchable(),
                TextColumn::make("product")->label("Produit")->searchable(),
                TextColumn::make("volume")
                    ->label("Litres")
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' ')
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total')->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' '))
                    ->searchable(),
                TextColumn::make("vehicle_registration")
                    ->label("Véhicule")
                    ->searchable(),
                TextColumn::make("status")
                    ->label("Status")
                    ->searchable()
                    ->badge()
                    ->color(
                        fn(?string $state): string => match ($state) {
                            "EN COURS" => "warning",
                            "LIVRÉ" => "success",
                            "LIVRÉ ET FACTURÉ" => "info",
                            "PAYÉ" => "success",
                            "CHARGÉ" => "success",
                            default => "gray",
                        }
                    ),
                TextColumn::make("load_date")
                    ->label("Date Chargement")
                    ->date("d-m-Y")
                    ->searchable(),
                TextColumn::make("unload_location")
                    ->label("Lieu Livraison")
                    ->toggleable()
                    ->hidden(fn() => $this->status === "EN COURS")
                    ->searchable(),
                TextColumn::make("client.nom")
                    ->label("Client")
                    ->toggleable()
                    ->hidden(fn() => $this->status === "EN COURS")
                    ->searchable(),
            ])
            ->emptyStateHeading(fn() => $this->status === "EN COURS" ? "Aucun Chargement n'est disponible" : "Aucune Livraison n'est disponible")
            ->filters([])
            ->actions([
                ActionGroup::make([
                    Action::make("edit")
                        ->label("Modifier")
                        ->hidden(fn (Load $record) => $record->status === 'LIVRÉ ET FACTURÉ')
                        ->icon("heroicon-m-eye")
                        ->action(
                            fn(Load $record, $livewire) => $livewire->dispatch(
                                "openModal",
                                "modals.load.edit-load",
                                ["load" => $record]
                            )
                        ),
                    Action::make("unload")
                        ->label("Livré")
                        ->hidden(fn (Load $record) => $record->status !== 'EN COURS')
                        ->icon("heroicon-m-arrow-down-on-square")
                        ->action(
                            fn(Load $record, $livewire) => $livewire->dispatch(
                                "openModal",
                                "modals.load.add-unload",
                                ["load" => $record]
                            )
                        ),
                    Action::make("delete")
                        ->label("Supprimé")
                        ->color("danger")
                        ->requiresConfirmation()
                        ->action(function (Load $record) {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                                if ($record->compartment_id) {
                                    $compartment = \App\Models\Compartment::find($record->compartment_id);
                                    if ($compartment) {
                                        $compartment->increment('quantity', $record->volume);
                                    }
                                }
                                $record->delete();
                            });
                            Notification::make()
                                ->title("Chargement supprimé")
                                ->success()
                                ->body(
                                    "Le chargement a été supprimé avec succés!"
                                )
                                ->send();
                        })
                        ->modalHeading("Chargement le dépôt")
                        ->icon("heroicon-m-trash")
                        ->modalDescription(
                            'Etes vous sûr(e) de vouloir supprimer ce Chargement ?, La supression de ce Chargement entrainera automatiquement la supression de l\'ensemble des informations liées'
                        )
                        ->modalSubmitActionLabel("Oui, Supprimé")
                        ->modalCancelActionLabel("Annulé")
                        ->icon("heroicon-m-trash"),
                ]),
            ])
            ->bulkActions([
                BulkAction::make("delete")
                    ->label("Supprimé les Chargements")
                    ->color("danger")
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->modalHeading("Supprimé les Chargements")
                    ->icon("heroicon-m-trash")
                    ->modalDescription(
                        'Etes vous sûr(e) de vouloir supprimer ces chargements ?, La supression de ces chargements entrainera automatiquement la supression de l\'ensemble des informations liées'
                    )
                    ->modalSubmitActionLabel("Oui, Supprimé")
                    ->modalCancelActionLabel("Annulé")
                    ->action(function (Collection $records) {
                        \Illuminate\Support\Facades\DB::transaction(function () use ($records) {
                            $records->each(function ($record) {
                                if ($record->compartment_id) {
                                    $compartment = \App\Models\Compartment::find($record->compartment_id);
                                    if ($compartment) {
                                        $compartment->increment('quantity', $record->volume);
                                    }
                                }
                                $record->delete();
                            });
                        });
                        Notification::make()
                            ->title("Chargements supprimés")
                            ->success()
                            ->body(
                                "Les Chargements ont été supprimé avec succés!"
                            )
                            ->send();
                    }),
            ]);
    }

    public function getLoads()
    {
        $query = Load::query();
        $query->where("status", $this->status);

        // Application des filtres personnalisés
        $locationField = ($this->status === 'EN COURS' || $this->status === 'CHARGÉ') ? 'load_location' : 'unload_location';
        $dateField = ($this->status === 'EN COURS' || $this->status === 'CHARGÉ') ? 'load_date' : 'unload_date';

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

        $this->applySortingToTableQuery($query);

        $loads = $query->get();

        return $loads;
    }

    public function printLoads()
    {
        $loads = $this->getLoads();

        $pdf = Pdf::loadView("livewire.load.print-loads", [
            "loads" => $loads,
            "status" => $this->status,
            "selectedLocations" => $this->selectedLocations,
            "selectedProduct" => $this->selectedProduct,
            "dateFrom" => $this->dateFrom,
            "dateUntil" => $this->dateUntil,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print $pdf->output(),
            "Liste_des_" . strtolower($this->status === 'EN COURS' ? 'chargements' : 'livraisons') . ".pdf"
        );
    }

    #[On("add-load"), On("update-load")]
    public function render()
    {
        return view("livewire.load.list-load");
    }

    public function getStatisticsProperty()
    {
        $loads = $this->getFilteredQuery()->get();

        $stats = [
            'count_by_product' => [],
            'litres_by_product' => [],
            'total_litres' => 0,
            'total_trucks' => $loads->count(),
        ];

        foreach ($loads as $load) {
            $product = $load->product ?? 'Inconnu';
            $volume = (int) $load->volume;

            if (!isset($stats['count_by_product'][$product])) {
                $stats['count_by_product'][$product] = 0;
                $stats['litres_by_product'][$product] = 0;
            }

            $stats['count_by_product'][$product]++;
            $stats['litres_by_product'][$product] += $volume;
            $stats['total_litres'] += $volume;
        }

        return $stats;
    }
}
