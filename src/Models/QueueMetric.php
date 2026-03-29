<?php

namespace CyberShield\Models;

use Illuminate\Database\Eloquent\Model;

class QueueMetric extends Model
{
    protected $table = 'cybershield_queue_metrics';
    public $timestamps = false;
    protected $fillable = ['job_name', 'status', 'execution_time', 'captured_at'];
    protected $casts = ['captured_at' => 'datetime'];
}
