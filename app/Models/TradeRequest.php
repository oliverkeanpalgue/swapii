<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'time',
        'place',
        'requestor_completed',
        'owner_completed',
        'user_id',
    ];

    protected $casts = [
        'time' => 'datetime',
    ];

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->diffForHumans()
        );
    }

    protected function time(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (! $value) {
                    return null;
                }

                return Carbon::parse($value)->tz('Asia/Manila');
            },
            set: function ($value) {
                if (! $value) {
                    return null;
                }

                return Carbon::parse($value)->tz('Asia/Manila');
            }
        );
    }

    public function images()
    {
        return $this->hasMany(TradeRequestItemImage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function cancellation()
    {
        return $this->hasOne(TradeCancellation::class);
    }
}
