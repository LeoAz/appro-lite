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

    public function mount($status)
    {
        $this->status = $status;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Load::query())
            ->modifyQueryUsing(
                fn(Builder $query) => $query->where("status", $this->status)
            )
            ->defaultSort("created_at", "desc")
            ->paginated([10, 25, 50, 100])
            ->selectable()
            ->columns([
                TextColumn::make("load_date")
                    ->label("Date Chargement")
                    ->date("d-m-Y")
                    ->searchable(),
                TextColumn::make("load_location")
                    ->label("Lieu Chargement")
                    ->searchable(),
                TextColumn::make("product")->label("Produit")->searchable(),
                TextColumn::make("capacity")->label("Litres")->searchable(),
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
        $query = $this->getFilteredTableQuery();
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
        ]);

        return response()->streamDownload(
            fn() => print $pdf->output(),
            "Liste_des_chargements.pdf"
        );
    }

    #[On("add-load"), On("update-load")]
    public function render()
    {
        return view("livewire.load.list-load");
    }
}
