<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReportImage extends Model
{
    use HasFactory;

    protected $fillable = ['user_report_id', 'image'];

    public function userReport()
    {
        return $this->belongsTo(UserReport::class);
    }
}
