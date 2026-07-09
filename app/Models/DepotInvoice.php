<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotInvoice extends Model
{
    protected $fillable = [
        'number',
        'date',
        'client_id',
        'depot_id',
        'product',
        'issuer_name',
        'total_amount',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function items()
    {
        return $this->hasMany(DepotInvoiceItem::class);
    }
}
