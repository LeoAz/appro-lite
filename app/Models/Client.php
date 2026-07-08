<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ["nom", "contact", "address", "initial_balance"];

    public function payments()
    {
        return $this->hasMany(ClientPayment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function loads()
    {
        return $this->hasMany(Load::class);
    }

    public function getBalanceAttribute()
    {
        $totalInvoiced = $this->invoices()->sum('total_amount');
        $totalPaid = $this->payments()->sum('amount');

        return ($this->initial_balance + $totalInvoiced) - $totalPaid;
    }
}
