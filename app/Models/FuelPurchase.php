<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product',
        'depot_id',
        'compartment_id',
        'quantity',
        'unit_price',
        'total_price',
        'purchase_date',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function compartment()
    {
        return $this->belongsTo(Compartment::class);
    }
}
