<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ["registration", "capacity", "carrier_id", "status"];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }
}
