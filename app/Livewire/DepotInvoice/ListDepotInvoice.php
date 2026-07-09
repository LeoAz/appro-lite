<?php

namespace App\Livewire\DepotInvoice;

use App\Models\DepotInvoice;
use App\Models\DepotInvoiceItem;
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
use Illuminate\Support\Facades\DB;

class ListDepotInvoice extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(DepotInvoice::query())
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
                TextColumn::make('client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('depot.name')
                    ->label('Dépôt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Montant Total')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ') . ' FCFA')
                    ->sortable(),
                TextColumn::make('items_sum_quantity')
                    ->label('Quantité Totale')
                    ->sum('items', 'quantity')
                    ->suffix(' L')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label('Modifier')
                        ->icon('heroicon-m-pencil-square')
                        ->action(fn (DepotInvoice $record) => $this->dispatch('openModal', component: 'modals.depot-invoice.edit-depot-invoice', arguments: ['invoice' => $record->id])),
                    Action::make('print')
                        ->label('Imprimer')
                        ->icon('heroicon-m-printer')
                        ->url(fn (DepotInvoice $record) => route('depot-invoices.print', $record))
                        ->openUrlInNewTab(),
                    Action::make('delete')
                        ->label('Supprimer')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (DepotInvoice $record) {
                            DB::transaction(function () use ($record) {
                                foreach ($record->items as $item) {
                                    // Remettre en stock
                                    $item->compartment->increment('quantity', $item->quantity);
                                }
                                $record->delete();
                            });

                            Notification::make()
                                ->title('Facture dépôt supprimée')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nouvelle Facture Dépôt')
                    ->icon('heroicon-m-plus')
                    ->action(fn () => $this->dispatch('openModal', component: 'modals.depot-invoice.add-depot-invoice')),
            ]);
    }

    #[On('depot-invoice-updated')]
    public function render()
    {
        return view('livewire.depot-invoice.list-depot-invoice');
    }
}
