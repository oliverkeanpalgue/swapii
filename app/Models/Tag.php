<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag',
        'code',
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class);
    }

    public function firstTag()
    {
        return $this->hasMany(UserTag::class, 'first_tag');
    }

    public function secondTag()
    {
        return $this->hasMany(UserTag::class, 'second_tag');
    }

    public function thirdTag()
    {
        return $this->hasMany(UserTag::class, 'third_tag');
    }
}
