<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_name',
        'address',
        'date_transaction',
        'interval',
        'date_time_transaction',
        'transaction_id',
        'total_amount_gross',
        'total_amount_net',
        'discount',
        'tax',
        'service',
        'status_sync',
        'cashier',
    ];

    protected $casts = [
        'date_transaction' => 'date',
        'date_time_transaction' => 'datetime',
        'total_amount_gross' => 'decimal:2',
        'total_amount_net' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'service' => 'decimal:2',
    ];
}