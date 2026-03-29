<?php

namespace CyberShield\Http\Controllers\Dashboard;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogController extends Controller
{
    /**
     * Display the log dashboard.
     */
    public function index(Request $request)
    {
        $logs = $this->getLogs($request);
        $channels = config('cybershield.logging.channels', []);

        return view('cybershield::dashboard.logs', compact('logs', 'channels'));
    }

    /**
     * Get and parse logs based on request filters.
     */
    protected function getLogs(Request $request): array
    {
        $channel = $request->get('channel', 'request');
        $logPath = storage_path("logs/cybershield/{$channel}.log");

        if (!File::exists($logPath)) {
            return [];
        }

        // Performance: Limit reading to last 1000 lines
        $lines = $this->readLastLines($logPath, 1000);
        $parsedLogs = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $parsed = $this->parseLogLine($line);
            if ($this->matchesFilters($parsed, $request)) {
                $parsedLogs[] = $parsed;
            }
        }

        // Reverse to show latest first
        return array_reverse($parsedLogs);
    }

    /**
     * Efficiently read last N lines of a file.
     */
    protected function readLastLines(string $filename, int $lines): array
    {
        $file = new \SplFileObject($filename, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $start = max(0, $totalLines - $lines);
        $file->seek($start);
        
        $result = [];
        while (!$file->eof()) {
            $result[] = $file->current();
            $file->next();
        }
        
        return $result;
    }

    /**
     * Parse a log line into a structured array.
     */
    protected function parseLogLine(string $line): array
    {
        // Default format: [{datetime}] {level} {ip} {method} {url} {status} {message}
        // Example: [2026-03-29 10:15:23] INFO 127.0.0.1 GET http://localhost/api/test 200 Request processed...
        
        preg_match('/^\[(?P<datetime>.*?)\]\s+(?P<level>\w+)\s+(?P<ip>[\d\.:a-fA-F]+)\s+(?P<method>\w+|N\/A)\s+(?P<url>.*?)\s+(?P<status>\d+|N\/A)\s+(?P<message>.*)$/', $line, $matches);

        return [
            'datetime' => $matches['datetime'] ?? 'N/A',
            'level' => $matches['level'] ?? 'INFO',
            'ip' => $matches['ip'] ?? 'N/A',
            'method' => $matches['method'] ?? 'N/A',
            'url' => $matches['url'] ?? 'N/A',
            'status' => $matches['status'] ?? 'N/A',
            'message' => $matches['message'] ?? $line,
        ];
    }

    /**
     * Check if a parsed log entry matches request filters.
     */
    protected function matchesFilters(array $log, Request $request): bool
    {
        if ($request->filled('ip') && !str_contains($log['ip'], $request->get('ip'))) {
            return false;
        }

        if ($request->filled('status') && $log['status'] !== $request->get('status')) {
            return false;
        }

        if ($request->filled('keyword') && !str_contains(strtolower($log['message']), strtolower($request->get('keyword')))) {
            return false;
        }

        if ($request->filled('date')) {
            if (!str_contains($log['datetime'], $request->get('date'))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Export logs to CSV.
     */
    public function exportCsv(Request $request)
    {
        $logs = $this->getLogs($request);
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Datetime', 'Level', 'IP', 'Method', 'URL', 'Status', 'Message']);

            foreach ($logs as $log) {
                fputcsv($file, [$log['datetime'], $log['level'], $log['ip'], $log['method'], $log['url'], $log['status'], $log['message']]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=cybershield_logs_" . date('Ymd_His') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    /**
     * Export logs to JSON.
     */
    public function exportJson(Request $request)
    {
        $logs = $this->getLogs($request);
        return response()->streamDownload(function() use ($logs) {
            echo json_encode($logs, JSON_PRETTY_PRINT);
        }, "cybershield_logs_" . date('Ymd_His') . ".json");
    }
}

