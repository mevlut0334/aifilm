<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'platform',
        'amount_paid',
        'currency',
        'gateway_transaction_id',
        'token_amount',
        'status',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'token_amount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
