@extends('cybershield::layouts.app')

@section('title', 'Security Overview')

@section('content')
<div class="header flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">CyberShield Security Dashboard</h1>
        <p class="text-sm text-slate-500">Real-time threat monitoring and system health</p>
    </div>
    <div class="flex items-center gap-4">
        <span id="refreshTimer" class="text-xs font-mono text-slate-400">Refreshing in 30s</span>
        <form action="{{ route('cybershield.refresh') }}" method="POST">
            @csrf
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition shadow-sm">
                Manual Refresh
            </button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-xs font-semibold text-slate-500 uppercase mb-2">Total Requests</h3>
        <div id="stat-total_requests" class="text-xl font-bold text-slate-900">{{ number_format($stats['total_requests']) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-xs font-semibold text-slate-500 uppercase mb-2">Blocked IPs</h3>
        <div id="stat-blocked_ips" class="text-xl font-bold text-red-600">{{ number_format($stats['blocked_ips']) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-xs font-semibold text-slate-500 uppercase mb-2">Threats Detected</h3>
        <div id="stat-threats_detected" class="text-xl font-bold text-orange-600">{{ number_format($stats['threats_detected']) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-xs font-semibold text-slate-500 uppercase mb-2">Active (1h)</h3>
        <div id="stat-active_threats" class="text-xl font-bold text-red-700">{{ number_format($stats['active_threats']) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-xs font-semibold text-slate-500 uppercase mb-2">Bot Attacks</h3>
        <div id="stat-botStats" class="text-xl font-bold text-indigo-600">{{ number_format($botStats) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-xs font-semibold text-slate-500 uppercase mb-2">Rate Limits</h3>
        <div id="stat-rateLimitStats" class="text-xl font-bold text-purple-600">{{ number_format($rateLimitStats) }}</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-slate-800">Traffic Pattern (24h)</h3>
            <span class="text-xs text-slate-400">Avg Response: <span id="stat-avg_response_time" class="font-bold text-slate-600">{{ $stats['avg_response_time'] }}ms</span></span>
        </div>
        <canvas id="trafficChart" height="100"></canvas>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Security Health</h3>
        <div class="space-y-6">
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-slate-600">Bot Protection</span>
                    <span class="text-sm font-bold text-green-600">Active</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5">
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: 100%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-slate-600">WAF Status</span>
                    <span class="text-sm font-bold text-green-600">Filtering</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5">
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: 100%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-slate-600">Malware Scanner</span>
                    <span id="stat-malwareStats" class="text-sm font-bold {{ $malwareStats > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ $malwareStats > 0 ? 'Threats Found' : 'Last Scan Clean' }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5">
                    <div class="{{ $malwareStats > 0 ? 'bg-red-500' : 'bg-indigo-500' }} h-1.5 rounded-full" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-6 border-t border-slate-100">
            <h4 class="text-sm font-bold text-slate-700 mb-4">System Metrics</h4>
            @if($metrics['system'])
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-slate-50 p-2 rounded-lg">
                    <div class="text-xs text-slate-400 mb-1">CPU</div>
                    <div id="metric-cpu" class="text-sm font-bold text-slate-800">{{ $metrics['system']->cpu_load }}%</div>
                </div>
                <div class="bg-slate-50 p-2 rounded-lg">
                    <div class="text-xs text-slate-400 mb-1">MEM</div>
                    <div id="metric-mem" class="text-sm font-bold text-slate-800">{{ $metrics['system']->memory_usage }}%</div>
                </div>
                <div class="bg-slate-50 p-2 rounded-lg">
                    <div class="text-xs text-slate-400 mb-1">DISK</div>
                    <div id="metric-disk" class="text-sm font-bold text-slate-800">{{ $metrics['system']->disk_usage }}%</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h3 class="font-semibold text-slate-800">Recent Threats</h3>
            <a href="{{ route('cybershield.logs.index') }}" class="text-xs text-indigo-600 hover:underline">View All Logs</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">IP</th>
                        <th class="px-6 py-3">Severity</th>
                        <th class="px-6 py-3">Time</th>
                    </tr>
                </thead>
                <tbody id="recentThreatsList" class="divide-y divide-slate-100">
                    @forelse($recentThreats as $threat)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-sm font-bold text-slate-700">{{ strtoupper($threat->threat_type) }}</td>
                        <td class="px-6 py-4 text-sm font-mono">{{ $threat->ip }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-bold rounded-lg {{ $threat->severity === 'high' ? 'bg-red-100 text-red-700' : ($threat->severity === 'medium' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ strtoupper($threat->severity) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400">{{ $threat->created_at ? $threat->created_at->diffForHumans() : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-400 italic">No threats detected recently.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50">
            <h3 class="font-semibold text-slate-800">API Performance</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-6 py-3">Endpoint</th>
                        <th class="px-6 py-3">Hits</th>
                        <th class="px-6 py-3 text-right">Avg Response</th>
                    </tr>
                </thead>
                <tbody id="apiMetricsList" class="divide-y divide-slate-100">
                    @forelse($metrics['api'] as $api)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-sm font-mono text-slate-600 truncate max-w-xs">{{ $api->endpoint }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-800">{{ number_format($api->hits) }}</td>
                        <td class="px-6 py-4 text-right text-sm text-slate-500">{{ number_format($api->avg_response_time, 2) }}ms</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-slate-400 italic">No API metrics captured.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let trafficChart;
    const ctx = document.getElementById('trafficChart').getContext('2d');
    
    function initChart(labels, data) {
        trafficChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Requests',
                    data: data,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.05)',
                    pointBackgroundColor: '#4f46e5',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    initChart({!! json_encode($trafficData->pluck('hour')) !!}, {!! json_encode($trafficData->pluck('count')) !!});

    // Auto-refresh logic
    let secondsLeft = 30;
    const timerElement = document.getElementById('refreshTimer');

    setInterval(() => {
        secondsLeft--;
        if (secondsLeft <= 0) {
            refreshData();
            secondsLeft = 30;
        }
        timerElement.innerText = `Refreshing in ${secondsLeft}s`;
    }, 1000);

    async function refreshData() {
        try {
            const response = await fetch(window.location.href, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();

            // Update stats
            for (const [key, value] of Object.entries(data.stats)) {
                const el = document.getElementById(`stat-${key}`);
                if (el) el.innerText = typeof value === 'number' ? value.toLocaleString() : value;
            }
            
            document.getElementById('stat-botStats').innerText = data.botStats.toLocaleString();
            document.getElementById('stat-rateLimitStats').innerText = data.rateLimitStats.toLocaleString();
            
            const malwareEl = document.getElementById('stat-malwareStats');
            if (data.malwareStats > 0) {
                malwareEl.innerText = 'Threats Found';
                malwareEl.className = 'text-sm font-bold text-red-600';
            } else {
                malwareEl.innerText = 'Last Scan Clean';
                malwareEl.className = 'text-sm font-bold text-slate-400';
            }

            // Update chart
            trafficChart.data.labels = data.trafficData.map(d => d.hour);
            trafficChart.data.datasets[0].data = data.trafficData.map(d => d.count);
            trafficChart.update();

            // Update system metrics
            if (data.metrics.system) {
                document.getElementById('metric-cpu').innerText = `${data.metrics.system.cpu_load}%`;
                document.getElementById('metric-mem').innerText = `${data.metrics.system.memory_usage}%`;
                document.getElementById('metric-disk').innerText = `${data.metrics.system.disk_usage}%`;
            }

            console.log('Dashboard data refreshed');
        } catch (error) {
            console.error('Failed to refresh dashboard data:', error);
        }
    }
</script>
@endsection