<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'parent_id',
        'payment_type',
        'is_advance',
        'amount',
        'payment_method',
        'date',
        'reference',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'is_advance' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function parent()
    {
        return $this->belongsTo(ClientPayment::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ClientPayment::class, 'parent_id');
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function depotInvoiceItems()
    {
        return $this->hasMany(DepotInvoiceItem::class);
    }
}
