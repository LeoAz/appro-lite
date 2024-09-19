<?php

namespace App\Livewire\Vehicle;

use App\Models\Vehicle;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ListVehicle extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Vehicle::query())
            ->defaultSort("created_at", "desc")
            ->paginated([10, 25, 50, 100])
            ->selectable()
            ->columns([
                TextColumn::make("registration")
                    ->label("N° Plaque")
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
                TextColumn::make("capacity")->label("Capacité")->searchable(),
                TextColumn::make("carrier.nom")
                    ->label("Transporteur")
                    ->searchable(),
                TextColumn::make("status")->badge()->searchable()->color(
                    fn(string $state): string => match ($state) {
                        "Disponible" => "success",
                        "En chargement" => "gray",
                    }
                ),
            ])
            ->emptyStateHeading('Aucun véhicule n\'est disponible')
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make("edit")
                        ->label("Modifier")
                        ->icon("heroicon-m-eye")
                        ->action(
                            fn(
                                Vehicle $record,
                                $livewire
                            ) => $livewire->dispatch(
                                "openModal",
                                "modals.vehicle.edit-vehicle",
                                ["vehicle" => $record]
                            )
                        ),
                    Action::make("delete")
                        ->label("Supprimer")
                        ->color("danger")
                        ->requiresConfirmation()
                        ->action(function (Vehicle $record) {
                            $record->delete();
                            Notification::make()
                                ->title("Véhicule supprimé")
                                ->success()
                                ->body(
                                    "Le vehicule a été supprimé avec succés!"
                                )
                                ->send();
                        })
                        ->modalHeading("Supprimé le véhicule")
                        ->icon("heroicon-m-trash")
                        ->modalDescription(
                            'Etes vous sûr(e) de vouloir supprimer ce vehicule ?, La supression de ce vehicule entrainera automatiquement la supression de l\'ensemble des chargements liés'
                        )
                        ->modalSubmitActionLabel("Oui, Supprimé")
                        ->modalCancelActionLabel("Annulé")
                        ->icon("heroicon-m-trash"),
                ]),
            ])
            ->bulkActions([
                BulkAction::make("delete")
                    ->label("Supprimé les véhicules")
                    ->color("danger")
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->modalHeading("Supprimé les véhicules")
                    ->icon("heroicon-m-trash")
                    ->modalDescription(
                        'Etes vous sûr(e) de vouloir supprimer ces véhicules ?, La supression de ces fournisseurs entrainera automatiquement la supression de l\'ensemble des chargements liés'
                    )
                    ->modalSubmitActionLabel("Oui, Supprimé")
                    ->modalCancelActionLabel("Annulé")
                    ->action(function (Collection $records) {
                        $records->each->delete();
                        Notification::make()
                            ->title("Véhicules supprimés")
                            ->success()
                            ->body(
                                "Les véhicules ont été supprimé avec succés!"
                            )
                            ->send();
                    }),
            ]);
    }

    #[On("add-vehicle"), On("update-vehicle")]
    public function render()
    {
        return view("livewire.vehicle.list-vehicle");
    }
}
