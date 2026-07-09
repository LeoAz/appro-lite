<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Depot;
use App\Models\Compartment;
use App\Models\DepotInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepotInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $client;
    protected $depot;
    protected $compartment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->client = Client::create(['nom' => 'Client Test']);
        $this->depot = Depot::create(['name' => 'Depot Test']);
        $this->compartment = Compartment::create([
            'depot_id' => $this->depot->id,
            'product' => 'GASOIL',
            'quantity' => 10000,
        ]);
    }

    /** @test */
    public function it_can_create_depot_invoice_and_update_stock()
    {
        $this->actingAs($this->user);

        Livewire::test(\App\Livewire\Modals\DepotInvoice\AddDepotInvoice::class)
            ->set('number', 'FAC-DEP-TEST-001')
            ->set('date', now())
            ->set('client_id', $this->client->id)
            ->set('depot_id', $this->depot->id)
            ->set('product', 'GASOIL')
            ->set('items', [
                [
                    'compartment_id' => $this->compartment->id,
                    'quantity' => 2000,
                    'unit_price' => 1000,
                    'total' => 2000000,
                ]
            ])
            ->set('total_amount', 2000000)
            ->call('create');

        $this->assertDatabaseHas('depot_invoices', [
            'number' => 'FAC-DEP-TEST-001',
            'total_amount' => 2000000,
        ]);

        $this->assertEquals(8000, $this->compartment->fresh()->quantity);
    }

    /** @test */
    public function it_restores_stock_when_depot_invoice_is_deleted()
    {
        $this->actingAs($this->user);

        $invoice = DepotInvoice::create([
            'number' => 'FAC-DEP-DELETE',
            'date' => now(),
            'client_id' => $this->client->id,
            'depot_id' => $this->depot->id,
            'product' => 'GASOIL',
            'total_amount' => 2000000,
        ]);

        $invoice->items()->create([
            'compartment_id' => $this->compartment->id,
            'quantity' => 2000,
            'unit_price' => 1000,
            'total' => 2000000,
        ]);

        $this->compartment->decrement('quantity', 2000);
        $this->assertEquals(8000, $this->compartment->fresh()->quantity);

        Livewire::test(\App\Livewire\DepotInvoice\ListDepotInvoice::class)
            ->callTableAction('delete', $invoice);

        $this->assertDatabaseMissing('depot_invoices', ['id' => $invoice->id]);
        $this->assertEquals(10000, $this->compartment->fresh()->quantity);
    }
}
