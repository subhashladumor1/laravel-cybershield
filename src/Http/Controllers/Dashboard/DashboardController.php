<?php

namespace CyberShield\Http\Controllers\Dashboard;

use Illuminate\Routing\Controller;
use CyberShield\Models\RequestLog;
use CyberShield\Models\IpActivity;
use CyberShield\Models\BlockedIp;
use CyberShield\Models\ThreatLog;
use CyberShield\Models\ApiMetric;
use CyberShield\Models\SystemMetric;
use CyberShield\Models\QueueMetric;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_requests' => RequestLog::count(),
            'blocked_ips' => BlockedIp::count(),
            'threats_detected' => ThreatLog::count(),
            'avg_response_time' => round(RequestLog::avg('response_time') ?? 0, 2),
            'active_threats' => ThreatLog::where('created_at', '>=', now()->subHours(1))->count(),
        ];

        // Additional metrics breakdown
        $botStats = ThreatLog::where('threat_type', 'like', 'bot%')->count();
        $rateLimitStats = ThreatLog::where('threat_type', 'rate_limit')->count();
        $malwareStats = ThreatLog::where('threat_type', 'malware')->count();

        $recentRequests = RequestLog::latest()->take(10)->get();
        $topIps = IpActivity::orderBy('total_requests', 'desc')->take(5)->get();
        $recentThreats = ThreatLog::latest()->take(10)->get();
        
        $metrics = [
            'system' => SystemMetric::latest()->first(),
            'api' => ApiMetric::orderBy('hits', 'desc')->take(10)->get(),
            'queue' => QueueMetric::latest()->take(5)->get(),
        ];

        // Chart data (last 24 hours)
        $trafficData = RequestLog::select(
            DB::raw('DATE_FORMAT(created_at, "%H:00") as hour'),
            DB::raw('count(*) as count')
        )
        ->where('created_at', '>=', now()->subHours(24))
        ->groupBy('hour')
        ->orderBy('hour')
        ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'stats' => $stats,
                'botStats' => $botStats,
                'rateLimitStats' => $rateLimitStats,
                'malwareStats' => $malwareStats,
                'recentRequests' => $recentRequests,
                'topIps' => $topIps,
                'recentThreats' => $recentThreats,
                'metrics' => $metrics,
                'trafficData' => $trafficData,
            ]);
        }

        return view('cybershield::dashboard.index', compact(
            'stats', 'botStats', 'rateLimitStats', 'malwareStats', 
            'recentRequests', 'topIps', 'recentThreats', 'metrics', 'trafficData'
        ));
    }

    public function refresh()
    {
        // For non-realtime refresh, we just redirect back to index
        return redirect()->route('cybershield.dashboard');
    }
}

