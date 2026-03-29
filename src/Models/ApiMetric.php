<?php

namespace CyberShield\Models;

use Illuminate\Database\Eloquent\Model;

class ApiMetric extends Model
{
    protected $table = 'cybershield_api_metrics';
    public $timestamps = false;
    protected $fillable = ['endpoint', 'method', 'hits', 'avg_response_time', 'captured_at'];
    protected $casts = ['captured_at' => 'datetime'];
}
