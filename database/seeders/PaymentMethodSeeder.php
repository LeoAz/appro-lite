<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = ['Espèces', 'Chèque', 'Virement', 'Autre'];

        foreach ($methods as $method) {
            \App\Models\PaymentMethod::updateOrCreate(['name' => $method]);
        }
    }
}
