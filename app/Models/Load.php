<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Load extends Model
{
    use HasFactory;

    protected $fillable = [
        "load_date",
        "city_id",
        "product",
        "capacity",
        "vehicle_id",
        "depot_id",
        "is_unload",
        "unload_date",
        "unload_location",
        "client_id",
        "status",
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
}
