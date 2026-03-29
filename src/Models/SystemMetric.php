<?php

namespace CyberShield\Models;

use Illuminate\Database\Eloquent\Model;

class SystemMetric extends Model
{
    protected $table = 'cybershield_system_metrics';
    public $timestamps = false;
    protected $fillable = ['cpu_load', 'memory_usage', 'disk_usage', 'captured_at'];
    protected $casts = ['captured_at' => 'datetime'];
}
