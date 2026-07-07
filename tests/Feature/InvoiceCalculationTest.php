<?php

namespace Tests\Feature;

use App\Models\Load;
use App\Models\User;
use App\Models\Depot;
use App\Models\Compartment;
use App\Enums\LoadStatus;
use App\Livewire\Modals\Invoice\AddInvoice;
use App\Livewire\Modals\Invoice\EditInvoice;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $load1;
    protected $load2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $depot = Depot::create(['name' => 'Dépôt Test']);
        $compartment = Compartment::create([
            'depot_id' => $depot->id,
            'product' => 'Essence',
            'quantity' => 100000,
        ]);

        // Créer des livraisons pour les tests
        $this->load1 = Load::create([
            'depot_id' => $depot->id,
            'compartment_id' => $compartment->id,
            'load_date' => now(),
            'load_location' => 'Conakry',
            'unload_date' => now(),
            'unload_location' => 'Kamsar',
            'product' => 'Essence',
            'volume' => 45000,
            'vehicle_registration' => 'RC-0001',
            'client_name' => 'Client Test',
            'status' => LoadStatus::Unloaded,
        ]);

        $this->load2 = Load::create([
            'depot_id' => $depot->id,
            'compartment_id' => $compartment->id,
            'load_date' => now(),
            'load_location' => 'Conakry',
            'unload_date' => now(),
            'unload_location' => 'Kamsar',
            'product' => 'Essence',
            'volume' => 30000,
            'vehicle_registration' => 'RC-0002',
            'client_name' => 'Client Test',
            'status' => LoadStatus::Unloaded,
        ]);
    }

    /** @test */
    public function it_calculates_item_missing_quantity_and_total_correctly_in_add_form()
    {
        Livewire::test(AddInvoice::class)
            ->set('client_name', 'Client Test')
            ->set('items', [
                [
                    'load_id' => $this->load1->id,
                    'quantity_delivered' => 44000,
                    'unit_price' => 1000,
                    'missing_quantity' => 0,
                    'total' => 0,
                ]
            ])
            ->fillForm([
                'items.0.quantity_delivered' => 44000,
                'items.0.unit_price' => 1000,
            ])
            ->assertSet('items.0.missing_quantity', 1000) // 45000 - 44000
            ->assertSet('items.0.total', 44000000); // 44000 * 1000
    }

    /** @test */
    public function it_calculates_invoice_totals_correctly_in_add_form()
    {
        Livewire::test(AddInvoice::class)
            ->set('client_name', 'Client Test')
            ->set('items', [
                [
                    'load_id' => $this->load1->id,
                    'quantity_delivered' => 44000,
                    'unit_price' => 1000,
                    'missing_quantity' => 1000,
                    'total' => 44000000,
                ],
                [
                    'load_id' => $this->load2->id,
                    'quantity_delivered' => 29500,
                    'unit_price' => 1000,
                    'missing_quantity' => 500,
                    'total' => 29500000,
                ]
            ])
            ->call('updateInvoiceTotals')
            ->assertSet('total_missing', 1500)
            ->assertSet('total_amount', 73500000);
    }

    /** @test */
    public function it_calculates_item_missing_quantity_and_total_correctly_in_edit_form()
    {
        $invoice = Invoice::create([
            'number' => 'FAC-TEST',
            'date' => now(),
            'client_name' => 'Client Test',
            'issuer_name' => 'CORRIDOR PETROLEUM',
            'total_missing' => 0,
            'total_amount' => 0,
        ]);

        $invoice->items()->create([
            'load_id' => $this->load1->id,
            'quantity_delivered' => 45000,
            'unit_price' => 0,
            'missing_quantity' => 0,
            'total' => 0,
        ]);

        Livewire::test(EditInvoice::class, ['invoice' => $invoice])
            ->fillForm([
                'items' => [
                    [
                        'load_id' => $this->load1->id,
                        'quantity_delivered' => 44000,
                        'unit_price' => 1000,
                        'missing_quantity' => 1000,
                        'total' => 44000000,
                    ]
                ]
            ])
            ->assertSet('items.0.missing_quantity', 1000)
            ->assertSet('items.0.total', 44000000);
    }

    /** @test */
    public function it_calculates_invoice_totals_correctly_in_edit_form()
    {
        $invoice = Invoice::create([
            'number' => 'FAC-TEST',
            'date' => now(),
            'client_name' => 'Client Test',
            'issuer_name' => 'CORRIDOR PETROLEUM',
            'total_missing' => 0,
            'total_amount' => 0,
        ]);

        $invoice->items()->create([
            'load_id' => $this->load1->id,
            'quantity_delivered' => 45000,
            'unit_price' => 0,
            'missing_quantity' => 0,
            'total' => 0,
        ]);
        $invoice->items()->create([
            'load_id' => $this->load2->id,
            'quantity_delivered' => 30000,
            'unit_price' => 0,
            'missing_quantity' => 0,
            'total' => 0,
        ]);

        Livewire::test(EditInvoice::class, ['invoice' => $invoice])
            ->set('items', [
                [
                    'load_id' => $this->load1->id,
                    'quantity_delivered' => 44000,
                    'unit_price' => 1000,
                    'missing_quantity' => 1000,
                    'total' => 44000000,
                ],
                [
                    'load_id' => $this->load2->id,
                    'quantity_delivered' => 29500,
                    'unit_price' => 1000,
                    'missing_quantity' => 500,
                    'total' => 29500000,
                ]
            ])
            ->call('updateInvoiceTotals')
            ->assertSet('total_missing', 1500)
            ->assertSet('total_amount', 73500000);
    }
    /** @test */
    public function it_generates_incremental_invoice_number()
    {
        $year = date('Y');

        // Create an existing invoice
        Invoice::create([
            'number' => "FAC-{$year}-00005",
            'date' => now(),
            'client_name' => 'Existing Client',
            'issuer_name' => 'CORRIDOR PETROLEUM',
            'total_missing' => 0,
            'total_amount' => 0,
        ]);

        Livewire::test(AddInvoice::class)
            ->assertSet('number', "FAC-{$year}-00006");
    }

    /** @test */
    public function it_generates_first_invoice_number_of_year()
    {
        $year = date('Y');
        Invoice::whereYear('date', $year)->delete();

        Livewire::test(AddInvoice::class)
            ->assertSet('number', "FAC-{$year}-00001");
    }
}
