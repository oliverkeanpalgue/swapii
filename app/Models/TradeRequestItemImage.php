<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeRequestItemImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
    ];

    public function trade_item()
    {
        return $this->belongsTo(TradeRequest::class);
    }
}
