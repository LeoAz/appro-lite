<?php

namespace App\Livewire\PaymentMethod;

use App\Models\PaymentMethod;
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
use Livewire\Attributes\Layout;

class ListPaymentMethod extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(PaymentMethod::query())
            ->defaultSort("created_at", "desc")
            ->columns([
                TextColumn::make("name")
                    ->label("Nom")
                    ->weight(FontWeight::SemiBold)
                    ->searchable(),
                TextColumn::make("created_at")
                    ->label("Date de création")
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading('Aucune méthode de règlement disponible')
            ->actions([
                ActionGroup::make([
                    Action::make("edit")
                        ->label("Modifier")
                        ->icon("heroicon-m-pencil-square")
                        ->action(
                            fn(
                                PaymentMethod $record,
                                $livewire
                            ) => $livewire->dispatch(
                                "openModal",
                                "modals.payment-method.add-payment-method",
                                ["paymentMethod" => $record]
                            )
                        ),
                    Action::make("delete")
                        ->label('Supprimer')
                        ->color("danger")
                        ->requiresConfirmation()
                        ->action(function (PaymentMethod $record) {
                            $record->delete();
                            Notification::make()
                                ->title("Méthode de règlement supprimée")
                                ->success()
                                ->send();
                        })
                        ->icon("heroicon-m-trash"),
                ]),
            ])
            ->headerActions([
                Action::make('add')
                    ->label('Ajouter une méthode')
                    ->icon('heroicon-m-plus')
                    ->action(fn($livewire) => $livewire->dispatch('openModal', 'modals.payment-method.add-payment-method')),
            ]);
    }

    #[On("payment-method-updated")]
    public function refresh()
    {
        // Refresh table
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view("livewire.payment-method.list-payment-method");
    }
}
