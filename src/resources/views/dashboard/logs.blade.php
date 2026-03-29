@extends('cybershield::layouts.app')

@section('title', 'Security Logs')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Security Logs</h1>
        <div class="flex space-x-2">
            <button onclick="window.location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Refresh Logs
            </button>
            <div class="flex space-x-2">
                <a href="{{ route('cybershield.logs.export.csv', request()->all()) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Export CSV
                </a>
                <a href="{{ route('cybershield.logs.export.json', request()->all()) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Export JSON
                </a>
            </div>
        </div>
    </div>

    <!-- Kibana Style Basic Search/Filter -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6">
        <form method="GET" action="{{ route('cybershield.logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Channel</label>
                <select name="channel" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50 p-2">
                    @foreach($channels as $chan => $enabled)
                        @if($enabled)
                            <option value="{{ $chan }}" {{ request('channel') == $chan ? 'selected' : '' }}>
                                {{ ucfirst($chan) }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">IP Address</label>
                <input type="text" name="ip" value="{{ request('ip') }}" placeholder="Filter by IP..." 
                       class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Code</label>
                <input type="text" name="status" value="{{ request('status') }}" placeholder="e.g. 403" 
                       class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keyword</label>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Search message..." 
                       class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-gray-50 p-2">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-bottom border-gray-200">
                        <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Timestamp</th>
                        <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">Level</th>
                        <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wider w-40">IP</th>
                        <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wider w-48">URL</th>
                        <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wider w-24 text-center">Status</th>
                        <th class="p-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition border-b border-gray-50">
                            <td class="p-4 text-sm font-mono text-gray-500 whitespace-nowrap">{{ $log['datetime'] }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs font-bold uppercase rounded-md 
                                    @if($log['level'] == 'CRITICAL' || $log['level'] == 'ALERT') bg-red-100 text-red-700
                                    @elseif($log['level'] == 'WARNING') bg-yellow-100 text-yellow-700
                                    @else bg-blue-100 text-blue-700 @endif">
                                    {{ $log['level'] }}
                                </span>
                            </td>
                            <td class="p-4 text-sm text-gray-700">{{ $log['ip'] }}</td>
                            <td class="p-4">
                                <span class="text-xs font-mono text-gray-500 block truncate max-w-xs" title="{{ $log['url'] }}">
                                    <span class="font-bold text-gray-800 mr-1">[{{ $log['method'] }}]</span>
                                    {{ $log['url'] }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <span class="text-sm font-bold {{ $log['status'] >= 400 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $log['status'] }}
                                </span>
                            </td>
                            <td class="p-4 text-sm text-gray-700 break-words">{{ $log['message'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-gray-400 italic">
                                No logs found for the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Premium Look Enhancements */
    .bg-gray-50 { background-color: #f8fafc; }
    .border-gray-200 { border-color: #e2e8f0; }
    .text-gray-600 { color: #475569; }
    .text-gray-800 { color: #1e293b; }
    
    /* Kibana vibe: dark headers and highlights */
    thead th {
        background-color: #f1f5f9;
        cursor: default;
    }
    
    tr:nth-child(even) {
        background-color: #fafbfd;
    }
</style>
@endsection
