<?php

namespace CyberShield\Models;

use Illuminate\Database\Eloquent\Model;

class IpActivity extends Model
{
    protected $table = 'cybershield_ip_activity';
    protected $fillable = ['ip', 'total_requests', 'last_seen', 'threat_score', 'metadata'];
    protected $casts = ['metadata' => 'json', 'last_seen' => 'datetime'];
}
