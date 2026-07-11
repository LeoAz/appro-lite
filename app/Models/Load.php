<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Load extends Model
{
    use HasFactory;

    protected $fillable = [
        "load_date",
        "load_location",
        "product",
        "volume",
        "vehicle_registration",
        "vehicle_id",
        "depot_id",
        "is_unload",
        "unload_date",
        "unload_location",
        "client_id",
        "client_name",
        "status",
        "compartment_id",
        "client_payment_id",
    ];

    protected $casts = [
        "load_date" => "datetime",
        "unload_date" => "datetime",
        "is_unload" => "boolean",
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function compartment()
    {
        return $this->belongsTo(Compartment::class);
    }

    public function payment()
    {
        return $this->belongsTo(ClientPayment::class, 'client_payment_id');
    }
}
