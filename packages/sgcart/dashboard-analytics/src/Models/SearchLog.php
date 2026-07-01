<?php

namespace SGCart\DashboardAnalytics\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $fillable = ['term', 'ip_address'];
}
