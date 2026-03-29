<?php

namespace CyberShield\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    protected $table = 'cybershield_blocked_ips';
    protected $fillable = ['ip', 'reason', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime'];
}
