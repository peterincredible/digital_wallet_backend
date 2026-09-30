<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    //
     // 1. Define the individual status constants
    public const TRANSACTION_TYPE_TOPUP = 1;
    public const TRANSACTION_TYPE_P2P = 2;

    public const STATUS_PENDING = 1;
    public const STATUS_COMPLETE = 2;

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class,"sender_id");
    }
    public function reciever(): BelongsTo
    {
        return $this->belongsTo(User::class,"reciever_id");
    }

    
}
