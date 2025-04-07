<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class NewPass extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'is_set',
    ];

    protected function createdAt(): Attribute
    {
        return new Attribute(
            get: fn ($value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
