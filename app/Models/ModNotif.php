<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModNotif extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'is_read', 'item_id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
