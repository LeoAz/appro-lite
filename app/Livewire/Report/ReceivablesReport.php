<?php

namespace App\Livewire\Report;

use App\Models\InvoiceItem;
use App\Models\Client;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceivablesReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $client_id;

    public function mount()
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('client_id')
                    ->label('Filtrer par Client')
                    ->options(Client::pluck('nom', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable())
                    ->placeholder('Tous les clients'),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InvoiceItem::query()
                    ->where('is_paid', false)
                    ->whereHas('invoice', function ($query) {
                        if ($this->client_id) {
                            $query->where('client_id', $this->client_id);
                        }
                    })
                    ->with(['invoice.client', 'delivery'])
            )
            ->columns([
                TextColumn::make('invoice.number')
                    ->label('N° Facture')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('invoice.date')
                    ->label('Date Facture')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('invoice.client.nom')
                    ->label('Client')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('delivery.vehicle_registration')
                    ->label('Véhicule')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('delivery.product')
                    ->label('Produit'),
                TextColumn::make('quantity_delivered')
                    ->label('Qté Facturée')
                    ->numeric()
                    ->suffix(' L'),
                TextColumn::make('total')
                    ->label('Montant Dû')
                    ->numeric()
                    ->suffix(' FCFA')
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total')),
            ])
            ->defaultSort('invoice.date', 'asc');
    }

    public function downloadPdf()
    {
        $items = InvoiceItem::query()
            ->where('is_paid', false)
            ->whereHas('invoice', function ($query) {
                if ($this->client_id) {
                    $query->where('client_id', $this->client_id);
                }
            })
            ->with(['invoice.client', 'delivery'])
            ->get();

        $total_receivable = $items->sum('total');

        $pdf = Pdf::loadView('livewire.report.print-receivables', [
            'items' => $items,
            'client' => $this->client_id ? Client::find($this->client_id) : null,
            'total_receivable' => $total_receivable,
            'date' => now(),
        ]);

        return response()->streamDownload(
            fn () => print $pdf->output(),
            "Etat_des_creances_" . now()->format('d_m_Y') . ".pdf"
        );
    }

    public function render()
    {
        return view('livewire.report.receivables-report');
    }
}
