<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'trade_request_id',
        'reason',
    ];

    public function tradeRequest()
    {
        return $this->belongsTo(TradeRequest::class);
    }
}
