<?php

namespace App\Livewire\Report;

use App\Models\City;
use App\Models\Load;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $type;

    public function mount($type = 'chargement')
    {
        $this->type = $type;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Load::query())
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->type === 'chargement') {
                    $query->where('status', 'EN COURS');
                } else {
                    $query->where('status', 'LIVRÉ');
                }
            })
            ->columns([
                TextColumn::make("load_date")
                    ->label("Date Chargement")
                    ->date("d-m-Y")
                    ->sortable(),
                TextColumn::make("load_location")
                    ->label("Lieu Chargement"),
                TextColumn::make("product")
                    ->label("Produit"),
                TextColumn::make("capacity")
                    ->label("Litres"),
                TextColumn::make("vehicle.registration")
                    ->label("Véhicule"),
                TextColumn::make("status")
                    ->label("Statut")
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'EN COURS' => 'info',
                        'LIVRÉ' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make("unload_date")
                    ->label("Date Livraison")
                    ->date("d-m-Y")
                    ->toggleable()
                    ->hidden(fn() => $this->type === 'chargement')
                    ->searchable(),
                TextColumn::make("unload_location")
                    ->label("Lieu Livraison")
                    ->toggleable()
                    ->hidden(fn() => $this->type === 'chargement')
                    ->searchable(),
                TextColumn::make("client_name")
                    ->label("Client")
                    ->toggleable()
                    ->hidden(fn() => $this->type === 'chargement')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->label('Produit')
                    ->options([
                        "FUEL" => "FUEL",
                        "SUPER" => "SUPER",
                        "GASOIL" => "GASOIL",
                    ]),
                Filter::make('locations')
                    ->form([
                        CheckboxList::make('locations')
                            ->label('Villes')
                            ->options(City::pluck('name', 'name'))
                            ->columns(4),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['locations'],
                            fn (Builder $query, $values): Builder => $query->whereIn($this->type === 'chargement' ? 'load_location' : 'unload_location', $values),
                        );
                    }),
                Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('Du')->native(false),
                        DatePicker::make('until')->label('Au')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate($this->type === 'chargement' ? 'load_date' : 'unload_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate($this->type === 'chargement' ? 'load_date' : 'unload_date', '<=', $date),
                            );
                    })
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->headerActions([
                Action::make('print')
                    ->label('Imprimer')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->action(fn () => $this->dispatch('print-report')),
            ])
            ->filtersTriggerAction(
                fn(\Filament\Tables\Actions\Action $action) => $action->button()->label("Filtre")->hidden()
            );
    }

    public function render()
    {
        return view('livewire.report.list-report');
    }
}
