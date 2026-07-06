<?php

namespace Tests\Feature;

use App\Models\Depot;
use App\Models\Compartment;
use App\Models\FuelPurchase;
use App\Models\Load;
use App\Models\User;
use App\Models\City;
use App\Livewire\Modals\Depot\AddCompartment;
use App\Livewire\Modals\FuelPurchase\AddFuelPurchase;
use App\Livewire\Modals\Load\AddLoad;
use App\Livewire\Modals\Load\AddUnload;
use App\Livewire\Report\ListReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    City::create(['name' => 'Conakry']);
});

test('on peut créer un dépôt et lui ajouter des compartiments', function () {
    $depot = Depot::create(['name' => 'Dépôt Central']);

    Livewire::test(AddCompartment::class, ['depot_id' => $depot->id])
        ->fillForm([
            'product' => 'Essence',
            'capacity' => 10000,
            'quantity' => 5000,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Compartment::where('depot_id', $depot->id)->count())->toBe(1);
    $compartment = Compartment::first();
    expect($compartment->product)->toBe('Essence')
        ->and((float)$compartment->quantity)->toBe(5000.0);
});

test('on ne peut pas avoir deux compartiments du même produit dans un dépôt', function () {
    $depot = Depot::create(['name' => 'Dépôt Central']);
    Compartment::create([
        'depot_id' => $depot->id,
        'product' => 'Essence',
        'capacity' => 10000,
        'quantity' => 5000,
    ]);

    Livewire::test(AddCompartment::class, ['depot_id' => $depot->id])
        ->fillForm([
            'product' => 'Essence',
            'capacity' => 5000,
            'quantity' => 1000,
        ])
        ->call('create')
        ->assertHasFormErrors(['product' => 'unique']);
});

test('un achat de carburant augmente le stock du compartiment', function () {
    $depot = Depot::create(['name' => 'Dépôt Central']);
    $compartment = Compartment::create([
        'depot_id' => $depot->id,
        'product' => 'Gasoil',
        'capacity' => 20000,
        'quantity' => 2000,
    ]);

    Livewire::test(AddFuelPurchase::class)
        ->set('data.depot_id', $depot->id)
        ->set('data.compartment_id', $compartment->id)
        ->fillForm([
            'quantity' => 3000,
            'unit_price' => 10,
            'purchase_date' => now(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $compartment->refresh();
    expect((float)$compartment->quantity)->toBe(5000.0);
    expect(FuelPurchase::count())->toBe(1);
});

test('un achat de carburant sans prix unitaire est possible', function () {
    $depot = Depot::create(['name' => 'Dépôt Central']);
    $compartment = Compartment::create([
        'depot_id' => $depot->id,
        'product' => 'Gasoil',
        'capacity' => 20000,
        'quantity' => 2000,
    ]);

    Livewire::test(AddFuelPurchase::class)
        ->set('data.depot_id', $depot->id)
        ->set('data.compartment_id', $compartment->id)
        ->fillForm([
            'quantity' => 3000,
            'unit_price' => null, // Prix unitaire non obligatoire
            'purchase_date' => now(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $compartment->refresh();
    expect((float)$compartment->quantity)->toBe(5000.0);
    $purchase = FuelPurchase::latest()->first();
    expect($purchase->unit_price)->toBeNull();
    expect($purchase->total_price)->toBeNull();
});

test('un chargement diminue le stock du compartiment', function () {
    $depot = Depot::create(['name' => 'Dépôt Central']);
    $compartment = Compartment::create([
        'depot_id' => $depot->id,
        'product' => 'Essence',
        'capacity' => 10000,
        'quantity' => 8000,
    ]);

    Livewire::test(AddLoad::class)
        ->set('depot_id', $depot->id)
        ->set('compartment_id', $compartment->id)
        ->set('load_location', 'Conakry')
        ->set('vehicle_registration', 'RC-1234-A')
        ->set('capacity', 3000)
        ->call('create')
        ->assertHasNoFormErrors();

    $compartment->refresh();
    expect((float)$compartment->quantity)->toBe(5000.0);
    expect(Load::count())->toBe(1);
});

test('un chargement échoue si le stock est insuffisant', function () {
    $depot = Depot::create(['name' => 'Dépôt Central']);
    $compartment = Compartment::create([
        'depot_id' => $depot->id,
        'product' => 'Essence',
        'capacity' => 10000,
        'quantity' => 1000,
    ]);

    Livewire::test(AddLoad::class)
        ->set('depot_id', $depot->id)
        ->set('compartment_id', $compartment->id)
        ->set('load_location', 'Conakry')
        ->set('vehicle_registration', 'RC-1234-A')
        ->set('capacity', 3000)
        ->call('create');

    $compartment->refresh();
    expect((float)$compartment->quantity)->toBe(1000.0);
    expect(Load::count())->toBe(0);
});

test('un chargement peut être transformé en livraison', function () {
    $depot = Depot::create(['name' => 'Dépôt Central']);
    $compartment = Compartment::create([
        'depot_id' => $depot->id,
        'product' => 'Essence',
        'capacity' => 10000,
        'quantity' => 8000,
    ]);

    $load = Load::create([
        'depot_id' => $depot->id,
        'compartment_id' => $compartment->id,
        'load_date' => now(),
        'load_location' => 'Conakry',
        'product' => 'Essence',
        'capacity' => 3000,
        'vehicle_registration' => 'RC-1234-A',
        'status' => 'CHARGÉ'
    ]);

    Livewire::test(AddUnload::class, ['load' => $load])
        ->set('unload_location', 'Lomé')
        ->set('client_name', 'Client ABC')
        ->call('unload')
        ->assertHasNoFormErrors();

    $load->refresh();
    expect($load->status)->toBe('LIVRÉ');
    expect($load->unload_location)->toBe('Lomé');
    expect($load->client_name)->toBe('Client ABC');
});

test('les statistiques du rapport sont correctes', function () {
    $depot = Depot::create(['name' => 'Dépôt Central']);
    $compartment = Compartment::create([
        'depot_id' => $depot->id,
        'product' => 'Essence',
        'capacity' => 20000,
        'quantity' => 15000,
    ]);

    // Créer 2 livraisons pour Client A (Essence)
    Load::create([
        'depot_id' => $depot->id,
        'compartment_id' => $compartment->id,
        'load_date' => now(),
        'load_location' => 'Conakry',
        'unload_date' => now(),
        'unload_location' => 'Conakry',
        'product' => 'Essence',
        'capacity' => 5000,
        'vehicle_registration' => 'RC-1',
        'client_name' => 'Client A',
        'status' => 'LIVRÉ'
    ]);

    Load::create([
        'depot_id' => $depot->id,
        'compartment_id' => $compartment->id,
        'load_date' => now(),
        'load_location' => 'Conakry',
        'unload_date' => now(),
        'unload_location' => 'Conakry',
        'product' => 'Essence',
        'capacity' => 3000,
        'vehicle_registration' => 'RC-2',
        'client_name' => 'Client A',
        'status' => 'LIVRÉ'
    ]);

    // Créer 1 livraison pour Client B (Essence)
    Load::create([
        'depot_id' => $depot->id,
        'compartment_id' => $compartment->id,
        'load_date' => now(),
        'load_location' => 'Conakry',
        'unload_date' => now(),
        'unload_location' => 'Conakry',
        'product' => 'Essence',
        'capacity' => 4000,
        'vehicle_registration' => 'RC-3',
        'client_name' => 'Client B',
        'status' => 'LIVRÉ'
    ]);

    $component = Livewire::test(ListReport::class, ['type' => 'livraison']);
    $stats = $component->get('statistics');

    expect($stats['total_trucks'])->toBe(3);
    expect($stats['total_litres'])->toBe(12000);
    expect($stats['count_by_client']['Client A'])->toBe(2);
    expect($stats['count_by_client']['Client B'])->toBe(1);
    expect($stats['litres_by_product']['Essence'])->toBe(12000);
});
