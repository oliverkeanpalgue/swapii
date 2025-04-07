<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReportImage extends Model
{
    protected $fillable = [
        'post_report_id',
        'image',
    ];
}
