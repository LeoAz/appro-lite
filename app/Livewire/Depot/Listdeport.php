<?php

namespace App\Livewire\Depot;

use App\Models\Depot;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class Listdeport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Depot::query())
            ->defaultSort("created_at", "desc")
            ->paginated([10, 25, 50, 100])
            ->selectable()
            ->columns([
                TextColumn::make("name")
                    ->label("Nom")
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
            ])
            ->emptyStateHeading('Aucun transporteur n\'est disponible')
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make("edit")
                        ->label("Modifier")
                        ->icon("heroicon-m-eye")
                        ->action(
                            fn(Depot $record, $livewire) => $livewire->dispatch(
                                "openModal",
                                "modals.depot.edit-depot",
                                ["depot" => $record]
                            )
                        ),
                    Action::make("delete")
                        ->label("Supprimé")
                        ->color("danger")
                        ->requiresConfirmation()
                        ->action(function (Depot $record) {
                            $record->delete();
                            Notification::make()
                                ->title("Dépôt supprimé")
                                ->success()
                                ->body("Le dépôt a été supprimé avec succés!")
                                ->send();
                        })
                        ->modalHeading("Supprimé le dépôt")
                        ->icon("heroicon-m-trash")
                        ->modalDescription(
                            'Etes vous sûr(e) de vouloir supprimer ce dépôt ?, La supression de ce dépôt entrainera automatiquement la supression de l\'ensemble des informations liées'
                        )
                        ->modalSubmitActionLabel("Oui, Supprimé")
                        ->modalCancelActionLabel("Annulé")
                        ->icon("heroicon-m-trash"),
                ]),
            ])
            ->bulkActions([
                BulkAction::make("delete")
                    ->label("Supprimé les dépôts")
                    ->color("danger")
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->modalHeading("Supprimé les dépôts")
                    ->icon("heroicon-m-trash")
                    ->modalDescription(
                        'Etes vous sûr(e) de vouloir supprimer ces dépôts ?, La supression de ces dépôts entrainera automatiquement la supression de l\'ensemble des informations liées'
                    )
                    ->modalSubmitActionLabel("Oui, Supprimé")
                    ->modalCancelActionLabel("Annulé")
                    ->action(function (Collection $records) {
                        $records->each->delete();
                        Notification::make()
                            ->title("dépôts supprimés")
                            ->success()
                            ->body("Les dépôts ont été supprimé avec succés!")
                            ->send();
                    }),
            ]);
    }

    #[On("add-depot"), On("update-depot")]
    public function render()
    {
        return view("livewire.depot.listdeport");
    }
}
