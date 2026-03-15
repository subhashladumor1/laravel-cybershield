<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberShield Security Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
        }

        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .accent-gradient {
            background: linear-gradient(135deg, #38bdf8 0%, #818cf8 100%);
        }
    </style>
</head>

<body class="p-8">
    <div class="max-w-7xl mx-auto">
        <header class="flex justify-between items-center mb-12">
            <div>
                <h1 class="text-4xl font-bold text-transparent bg-clip-text accent-gradient inline-block">Laravel
                    CyberShield</h1>
                <p class="text-slate-400 mt-2">Enterprise-Grade Security Engine</p>
            </div>
            <div class="glass px-6 py-3 rounded-2xl flex items-center gap-4">
                <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                <span class="font-semibold text-sm uppercase tracking-wider">System: Protected</span>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="glass p-6 rounded-3xl">
                <p class="text-slate-400 text-sm font-medium mb-1">Blocked IPs</p>
                <h2 class="text-3xl font-bold">{{ $stats['blocked_ips'] }}</h2>
            </div>
            <div class="glass p-6 rounded-3xl">
                <p class="text-slate-400 text-sm font-medium mb-1">Attacks Prevented</p>
                <h2 class="text-3xl font-bold">{{ $stats['attacks_prevented'] }}</h2>
            </div>
            <div class="glass p-6 rounded-3xl">
                <p class="text-slate-400 text-sm font-medium mb-1">API Threats</p>
                <h2 class="text-3xl font-bold">{{ $stats['api_threats'] }}</h2>
            </div>
            <div class="glass p-6 rounded-3xl">
                <p class="text-slate-400 text-sm font-medium mb-1">Malware Status</p>
                <h2 class="text-3xl font-bold text-green-400">{{ $stats['malware_status'] }}</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 glass rounded-3xl p-8">
                <h3 class="text-xl font-bold mb-6">Recent Security Events</h3>
                <div class="space-y-4">
                    @foreach(range(1, 5) as $i)
                        <div
                            class="flex items-center justify-between p-4 bg-slate-800/50 rounded-2xl border border-white/5">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">SQL Injection Attempt Detected</p>
                                    <p class="text-xs text-slate-500">IP: 192.168.1.{{ rand(1, 255) }} • {{ rand(1, 60) }}
                                        mins ago</p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-bold uppercase py-1 px-3 bg-red-500/10 text-red-500 rounded-full">Blocked</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="glass rounded-3xl p-8">
                <h3 class="text-xl font-bold mb-6">Threat level</h3>
                <div class="flex flex-col items-center justify-center h-64">
                    <div class="relative w-48 h-48">
                        <svg class="w-full h-full" viewBox="0 0 100 100">
                            <circle class="text-slate-800" stroke-width="10" stroke="currentColor" fill="transparent"
                                r="40" cx="50" cy="50" />
                            <circle class="text-sky-500" stroke-width="10" stroke-dasharray="251.2"
                                stroke-dashoffset="60" stroke-linecap="round" stroke="currentColor" fill="transparent"
                                r="40" cx="50" cy="50" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-4xl font-bold">Low</span>
                            <span class="text-xs text-slate-500">Overall risk</span>
                        </div>
                    </div>
                </div>
                <div class="mt-8">
                    <button
                        class="w-full py-4 rounded-2xl accent-gradient font-bold shadow-lg shadow-sky-500/20 hover:scale-[1.02] transition-transform">Run
                        Deep Scan</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>