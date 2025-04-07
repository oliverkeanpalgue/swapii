<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'user_id',
        'approval',
        'category_id',
        'item_description',
        'item_name',
        'preferred_items',
        'view_count',

    ];

    protected function tradeRequestTotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tradeRequests()->count(),
        );
    }

    public function images()
    {
        return $this->hasMany(ItemImage::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tradeRequests()
    {
        return $this->hasMany(TradeRequest::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function rejectReasons()
    {
        return $this->hasMany(PostRejectReason::class);
    }

    public function log()
    {
        return $this->hasOne(ActionLog::class);
    }

    public function reports()
    {
        return $this->hasMany(PostReport::class);
    }

    public function preferences()
    {
        return $this->hasOne(Preference::class);
    }
}
