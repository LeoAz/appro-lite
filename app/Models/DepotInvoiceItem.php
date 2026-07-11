<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotInvoiceItem extends Model
{
    protected $fillable = [
        'depot_invoice_id',
        'compartment_id',
        'client_payment_id',
        'is_paid',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    public function payment()
    {
        return $this->belongsTo(ClientPayment::class, 'client_payment_id');
    }

    public function depotInvoice()
    {
        return $this->belongsTo(DepotInvoice::class);
    }

    public function compartment()
    {
        return $this->belongsTo(Compartment::class);
    }
}
