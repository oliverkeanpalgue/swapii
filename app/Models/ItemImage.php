<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(Item::class);
    }
}
