<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'bl_number',
        'load_id',
        'client_payment_id',
        'quantity_delivered',
        'unit_price',
        'missing_quantity',
        'total',
        'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Load::class, 'load_id');
    }

    public function payment()
    {
        return $this->belongsTo(ClientPayment::class, 'client_payment_id');
    }
}
