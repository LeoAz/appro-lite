<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotInvoiceItem extends Model
{
    protected $fillable = [
        'depot_invoice_id',
        'compartment_id',
        'quantity',
        'unit_price',
        'total',
    ];

    public function depotInvoice()
    {
        return $this->belongsTo(DepotInvoice::class);
    }

    public function compartment()
    {
        return $this->belongsTo(Compartment::class);
    }
}
