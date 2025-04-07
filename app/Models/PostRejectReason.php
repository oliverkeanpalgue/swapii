<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostRejectReason extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'reason'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
