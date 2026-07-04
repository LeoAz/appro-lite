<?php

namespace App\Livewire\Load;

use App\Models\Load;
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

    public function mount($status)
    {
        $this->status = $status;
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedLocations', 'selectedProduct', 'dateFrom', 'dateUntil'])) {
            $this->resetTable();
        }
    }

    public function getFilteredQuery()
    {
        $query = Load::query()->where("status", $this->status);

        // Application des filtres personnalisés
        $locationField = $this->status === 'EN COURS' ? 'load_location' : 'unload_location';
        $dateField = $this->status === 'EN COURS' ? 'load_date' : 'unload_date';

        return $query->when($this->selectedLocations, fn($q) => $q->whereIn($locationField, $this->selectedLocations))
            ->when($this->selectedProduct, fn($q) => $q->where('product', $this->selectedProduct))
            ->when($this->dateFrom, fn($q) => $q->whereDate($dateField, '>=', $this->dateFrom))
            ->when($this->dateUntil, fn($q) => $q->whereDate($dateField, '<=', $this->dateUntil));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->defaultSort("created_at", "desc")
            ->paginated([10, 25, 50, 100])
            ->selectable()
            ->columns([
                TextColumn::make("index")
                    ->label("N°")
                    ->rowIndex(),
                TextColumn::make("load_date")
                    ->label("Date Chargement")
                    ->date("d-m-Y")
                    ->searchable(),
                TextColumn::make("load_location")
                    ->label("Lieu Chargement")
                    ->searchable(),
                TextColumn::make("product")->label("Produit")->searchable(),
                TextColumn::make("capacity")
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
                            "EN COURS" => "success",
                            "LIVRÉ" => "gray",
                            default => "gray",
                        }
                    ),
                TextColumn::make("unload_date")
                    ->label("Date Livraison")
                    ->date("d-m-Y")
                    ->toggleable()
                    ->hidden(fn() => $this->status === "EN COURS")
                    ->searchable(),
                TextColumn::make("unload_location")
                    ->label("Lieu Livraison")
                    ->toggleable()
                    ->hidden(fn() => $this->status === "EN COURS")
                    ->searchable(),
                TextColumn::make("client_name")
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
                        ->hidden(fn () => $this->status === 'LIVRÉ')
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
                            $record->delete();
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
                        $records->each->delete();
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
        $locationField = $this->status === 'EN COURS' ? 'load_location' : 'unload_location';
        $dateField = $this->status === 'EN COURS' ? 'load_date' : 'unload_date';

        $query->when($this->selectedLocations, fn($q) => $q->whereIn($locationField, $this->selectedLocations))
            ->when($this->selectedProduct, fn($q) => $q->where('product', $this->selectedProduct))
            ->when($this->dateFrom, fn($q) => $q->whereDate($dateField, '>=', $this->dateFrom))
            ->when($this->dateUntil, fn($q) => $q->whereDate($dateField, '<=', $this->dateUntil));

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
        ]);

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
            $capacity = (int) $load->capacity;

            if (!isset($stats['count_by_product'][$product])) {
                $stats['count_by_product'][$product] = 0;
                $stats['litres_by_product'][$product] = 0;
            }

            $stats['count_by_product'][$product]++;
            $stats['litres_by_product'][$product] += $capacity;
            $stats['total_litres'] += $capacity;
        }

        return $stats;
    }
}
