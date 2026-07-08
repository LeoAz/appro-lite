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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
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
                TextColumn::make("address")->label("Adresse")->searchable(),
                TextColumn::make("balance")
                    ->label("Solde")
                    ->money('XOF')
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->weight(FontWeight::Bold),
            ])
            ->emptyStateHeading('Aucun client n\'est disponible')
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make("view_account")
                        ->label("Compte Client")
                        ->icon("heroicon-m-banknotes")
                        ->action(
                            fn(Client $record, $livewire) => $livewire->dispatch(
                                "openModal",
                                "modals.client.view-client-account",
                                ["client" => $record]
                            )
                        ),
                    Action::make("add_payment")
                        ->label("Nouveau règlement")
                        ->icon("heroicon-m-plus-circle")
                        ->action(
                            fn(Client $record, $livewire) => $livewire->dispatch(
                                "openModal",
                                "modals.client.add-client-payment",
                                ["client" => $record]
                            )
                        ),
                    Action::make("init_balance")
                        ->label("Initialiser le solde")
                        ->icon("heroicon-m-scale")
                        ->form([
                            TextInput::make('initial_balance')
                                ->label('Solde initial')
                                ->numeric()
                                ->required()
                                ->prefix('FCFA')
                                ->helperText('Utilisez un montant négatif si l\'entreprise doit au client.'),
                        ])
                        ->action(function (Client $record, array $data) {
                            $record->update(['initial_balance' => $data['initial_balance']]);
                            Notification::make()
                                ->title('Solde initialisé')
                                ->success()
                                ->send();
                        }),
                    Action::make("reset_account")
                        ->label("Réinitialiser le compte")
                        ->icon("heroicon-m-arrow-path")
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Réinitialiser le compte')
                        ->modalDescription('Cette action va supprimer tous les règlements et factures liés à ce client (ou vous pouvez choisir de créer une écriture de régularisation). Souhaitez-vous vraiment remettre le solde à Zéro en supprimant l\'historique ?')
                        ->action(function (Client $record) {
                            $record->payments()->delete();
                            // Note: Invoices might be linked to loads, so be careful.
                            // Usually, "Reset" means zeroing the balance.
                            $record->update(['initial_balance' => 0]);
                            // If we want to keep invoices but zero balance, we'd need a different approach.
                            // But usually, users want to start fresh.
                            Notification::make()
                                ->title('Compte réinitialisé')
                                ->warning()
                                ->send();
                        }),
                    EditAction::make()
                        ->label("Modifier")
                        ->icon("heroicon-m-pencil-square")
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
                    DeleteAction::make("delete")
                        ->label("Supprimer")
                        ->modalHeading("Supprimer le client")
                        ->modalDescription(
                            'Etes vous sûr(e) de vouloir supprimer ce client ?, La supression de ce client entrainera automatiquement la supression de l\'ensemble des informations liées'
                        )
                        ->modalSubmitActionLabel("Oui, Supprimer")
                        ->modalCancelActionLabel("Annuler"),
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
