<?php

namespace App\Livewire\Carrier;

use App\Models\Carrier;
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

class ListCarrier extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Carrier::query())
            ->defaultSort("created_at", "desc")
            ->paginated([10, 25, 50, 100])
            ->selectable()
            ->columns([
                TextColumn::make("nom")
                    ->label("Nom")
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
                TextColumn::make("contact")->label("Contact")->searchable(),
                TextColumn::make("address")->label("addresse")->searchable(),
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
                            fn(
                                Carrier $record,
                                $livewire
                            ) => $livewire->dispatch(
                                "openModal",
                                "modals.carrier.edit-carrier",
                                ["carrier" => $record]
                            )
                        ),
                    Action::make("delete")
                        ->label("Supprimé")
                        ->color("danger")
                        ->requiresConfirmation()
                        ->action(function (Carrier $record) {
                            $record->delete();
                            Notification::make()
                                ->title("Transporteur supprimé")
                                ->success()
                                ->body(
                                    "Le transporteur a été supprimé avec succés!"
                                )
                                ->send();
                        })
                        ->modalHeading("Supprimé le transporteur")
                        ->icon("heroicon-m-trash")
                        ->modalDescription(
                            'Etes vous sûr(e) de vouloir supprimer ce transporteur ?, La supression de ce transporteur entrainera automatiquement la supression de l\'ensemble des informations liées'
                        )
                        ->modalSubmitActionLabel("Oui, Supprimé")
                        ->modalCancelActionLabel("Annulé")
                        ->icon("heroicon-m-trash"),
                ]),
            ])
            ->bulkActions([
                BulkAction::make("delete")
                    ->label("Supprimé les transporteurs")
                    ->color("danger")
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->modalHeading("Supprimé les transporteurs")
                    ->icon("heroicon-m-trash")
                    ->modalDescription(
                        'Etes vous sûr(e) de vouloir supprimer ces transporteurs ?, La supression de ces transporteurs entrainera automatiquement la supression de l\'ensemble des informations liées'
                    )
                    ->modalSubmitActionLabel("Oui, Supprimé")
                    ->modalCancelActionLabel("Annulé")
                    ->action(function (Collection $records) {
                        $records->each->delete();
                        Notification::make()
                            ->title("Transporteurs supprimés")
                            ->success()
                            ->body(
                                "Les transporteurs ont été supprimé avec succés!"
                            )
                            ->send();
                    }),
            ]);
    }

    #[On("add-carrier"), On("update-carrier")]
    public function render()
    {
        return view("livewire.carrier.list-carrier");
    }
}
