<?php

namespace CyberShield\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $table = 'cybershield_requests_logs';
    public $timestamps = false;
    protected $fillable = ['ip', 'url', 'method', 'status_code', 'response_time', 'user_agent', 'payload'];
    protected $casts = ['payload' => 'json'];
}
