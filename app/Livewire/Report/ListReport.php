<?php

namespace App\Livewire\Report;

use App\Models\Load;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Load::query())
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
                    ->color(fn (string $state): string => match ($state) {
                        'EN COURS' => 'success',
                        'LIVRÉ' => 'gray',
                    }),
                TextColumn::make("unload_date")
                    ->label("Date Livraison")
                    ->date("d-m-Y"),
                TextColumn::make("unload_location")
                    ->label("Lieu Livraison"),
                TextColumn::make("client_name")
                    ->label("Client"),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->label('Produit')
                    ->options([
                        "FUEL" => "FUEL",
                        "SUPER" => "SUPER",
                        "GASOIL" => "GASOIL",
                    ]),
                Filter::make('load_location')
                    ->form([
                        TextInput::make('load_location')
                            ->label('Lieu de chargement'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['load_location'],
                            fn (Builder $query, $value): Builder => $query->where('load_location', 'like', "%{$value}%"),
                        );
                    }),
                Filter::make('unload_location')
                    ->form([
                        TextInput::make('unload_location')
                            ->label('Lieu de livraison'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['unload_location'],
                            fn (Builder $query, $value): Builder => $query->where('unload_location', 'like', "%{$value}%"),
                        );
                    }),
                Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('Du'),
                        DatePicker::make('until')->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('load_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('load_date', '<=', $date),
                            );
                    })
            ]);
    }

    public function render()
    {
        return view('livewire.report.list-report');
    }
}
