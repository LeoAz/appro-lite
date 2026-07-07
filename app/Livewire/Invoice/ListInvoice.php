<?php

namespace App\Livewire\Invoice;

use App\Models\Invoice;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class ListInvoice extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Invoice::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->label('Numéro')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('issuer_name')
                    ->label('Émetteur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_missing')
                    ->label('Total Manquant')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . ' L')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Montant Total')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . ' FCFA')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Voir')
                        ->icon('heroicon-m-eye')
                        ->action(fn (Invoice $record) => $this->dispatch('openModal', component: 'modals.invoice.view-invoice', arguments: ['invoice' => $record->id])),
                    Action::make('edit')
                        ->label('Modifier')
                        ->icon('heroicon-m-pencil-square')
                        ->action(fn (Invoice $record) => $this->dispatch('openModal', component: 'modals.invoice.edit-invoice', arguments: ['invoice' => $record->id])),
                    Action::make('print')
                        ->label('Imprimer')
                        ->icon('heroicon-m-printer')
                        ->url(fn (Invoice $record) => route('invoices.print', $record))
                        ->openUrlInNewTab(),
                    Action::make('delete')
                        ->label('Supprimer')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Invoice $record) {
                            $record->delete();
                            Notification::make()
                                ->title('Facture supprimée')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->bulkActions([
                //
            ]);
    }

    #[On('invoice-updated')]
    public function render()
    {
        return view('livewire.invoice.list-invoice');
    }
}
