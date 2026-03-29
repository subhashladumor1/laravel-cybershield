<?php

namespace CyberShield\Models;

use Illuminate\Database\Eloquent\Model;

class ThreatLog extends Model
{
    protected $table = 'cybershield_threat_logs';
    public $timestamps = false;
    protected $fillable = ['ip', 'threat_type', 'severity', 'details'];
    protected $casts = ['details' => 'json'];
}
