<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reporter_id',
        'concern',
        'description',
        'other_concern',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function images()
    {
        return $this->hasMany(UserReportImage::class);
    }
}
