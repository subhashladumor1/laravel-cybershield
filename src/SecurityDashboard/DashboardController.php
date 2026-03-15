<?php

namespace CyberShield\SecurityDashboard;

use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'blocked_ips' => 1250,
            'attacks_prevented' => 45800,
            'api_threats' => 210,
            'malware_status' => 'Clean',
            'last_scan' => now()->diffForHumans(),
        ];

        return view('cybershield::dashboard.index', compact('stats'));
    }
}
