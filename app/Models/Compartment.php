<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'depot_id',
        'product',
        'quantity',
        'capacity',
    ];

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function loads()
    {
        return $this->hasMany(Load::class);
    }

    public function fuelPurchases()
    {
        return $this->hasMany(FuelPurchase::class);
    }
}
