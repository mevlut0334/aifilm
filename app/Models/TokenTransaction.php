<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'reference_id',
        'reference_type',
        'note',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
