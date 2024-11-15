<?php

namespace App\Livewire\Client;

use App\Models\Client;
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

class ListClient extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Client::query())
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
                                Client $record,
                                $livewire
                            ) => $livewire->dispatch(
                                "openModal",
                                "modals.client.edit-client",
                                ["client" => $record]
                            )
                        ),
                    Action::make("delete")
                        ->label("")
                        ->color("danger")
                        ->requiresConfirmation()
                        ->action(function (Client $record) {
                            $record->delete();
                            Notification::make()
                                ->title("Client supprimé")
                                ->success()
                                ->body("Le client a été supprimé avec succés!")
                                ->send();
                        })
                        ->modalHeading("Supprimé le client")
                        ->icon("heroicon-m-trash")
                        ->modalDescription(
                            'Etes vous sûr(e) de vouloir supprimer ce client ?, La supression de ce client entrainera automatiquement la supression de l\'ensemble des informations liées'
                        )
                        ->modalSubmitActionLabel("Oui, Supprimé")
                        ->modalCancelActionLabel("Annulé")
                        ->icon("heroicon-m-trash"),
                ]),
            ])
            ->bulkActions([
                BulkAction::make("delete")
                    ->label("Supprimé les clients")
                    ->color("danger")
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->modalHeading("Supprimé les clients")
                    ->icon("heroicon-m-trash")
                    ->modalDescription(
                        'Etes vous sûr(e) de vouloir supprimer ces clients ?, La supression de ces clients entrainera automatiquement la supression de l\'ensemble des informations liées'
                    )
                    ->modalSubmitActionLabel("Oui, Supprimé")
                    ->modalCancelActionLabel("Annulé")
                    ->action(function (Collection $records) {
                        $records->each->delete();
                        Notification::make()
                            ->title("Clients supprimés")
                            ->success()
                            ->body("Les clients ont été supprimé avec succés!")
                            ->send();
                    }),
            ]);
    }

    #[On("add-client"), On("update-client")]
    public function render()
    {
        return view("livewire.client.list-client");
    }
}
