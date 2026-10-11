<?php

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

new class extends Component
{
    public string $operatingSystem = '';
    public array $dbHealth = [];
    public array $httpHealth = [];
    public array $reverbHealth = [];

    public function mount(): void
    {
        $this->operatingSystem = $this->describeOs();
        $this->checkHealth();
    }

    /** "Windows 11 (10.0.26200)" instead of php_uname's bare "Windows NT 10.0". */
    protected function describeOs(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $build = (int) (explode('.', php_uname('v'))[2] ?? 0);
            // Windows 11 still reports NT 10.0; builds from 22000 are Windows 11.
            $name = $build >= 22000 ? 'Windows 11' : 'Windows ' . php_uname('r');

            return $name . ($build ? " (build {$build})" : '');
        }

        return php_uname('s') . ' ' . php_uname('r');
    }

    /**
     * TCP probe that behaves the same on Windows and Linux.
     *
     * - 0.0.0.0 / :: are bind addresses, not connectable on Windows, so they map to loopback.
     * - "localhost" can resolve to ::1 first on Windows while the server only listens on
     *   IPv4 (and vice versa), so both loopback addresses are tried.
     */
    protected function probe(?string $host, int $port, float $timeout = 1.5): bool
    {
        $host = trim((string) $host, '[] ');

        $targets = in_array(strtolower($host), ['', '0.0.0.0', '::', 'localhost', '127.0.0.1', '::1'], true)
            ? ['127.0.0.1', '[::1]']
            : [$host];

        foreach ($targets as $target) {
            $socket = @stream_socket_client("tcp://{$target}:{$port}", $errno, $errstr, $timeout);

            if (is_resource($socket)) {
                fclose($socket);

                return true;
            }
        }

        return false;
    }

    public function checkHealth(): void
    {
        // 1. Check Database
        try {
            $startTime = microtime(true);
            $connection = DB::connection();
            $connection->getPdo();
            $connection->select('select 1'); // proves the DB actually answers, not just that a handle exists
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            $this->dbHealth = [
                'status' => 'ok',
                'message' => 'Database Connected',
                'latency' => $latency . ' ms',
                'driver' => $connection->getDriverName(),
            ];
        } catch (\Throwable $e) {
            $this->dbHealth = [
                'status' => 'error',
                'message' => 'Connection Failed: ' . $e->getMessage(),
                'latency' => 'N/A',
                'driver' => 'Unknown',
            ];
        }

        // 2. Check HTTP Server (the host/port this page was actually served from)
        try {
            $host = request()->getHost() ?: '127.0.0.1';
            $port = (int) (request()->getPort() ?: (request()->isSecure() ? 443 : 80));

            if ($this->probe($host, $port)) {
                $this->httpHealth = [
                    'status'  => 'ok',
                    'code'    => 200,
                    'message' => "Server listening on {$host}:{$port}",
                ];
            } else {
                $this->httpHealth = [
                    'status'  => 'error',
                    'code'    => 500,
                    'message' => "Port {$port} unreachable on {$host}",
                ];
            }
        } catch (\Throwable $e) {
            $this->httpHealth = [
                'status'  => 'error',
                'code'    => 500,
                'message' => 'HTTP Check Failed: ' . $e->getMessage(),
            ];
        }

        // 3. Check Laravel Reverb Server
        try {
            // Prefer the client-facing host/port from .env; the server bind host is often 0.0.0.0.
            $reverbHost = config('broadcasting.connections.reverb.options.host') ?: config('reverb.servers.reverb.host', '127.0.0.1');
            $reverbPort = (int) (config('broadcasting.connections.reverb.options.port') ?: config('reverb.servers.reverb.port', 8080));

            if ($this->probe($reverbHost, $reverbPort, 2)) {
                $this->reverbHealth = [
                    'status' => 'ok',
                    'message' => "Running on {$reverbHost}:{$reverbPort}",
                ];
            } else {
                $this->reverbHealth = [
                    'status' => 'error',
                    'message' => "Reverb Offline on port {$reverbPort}",
                ];
            }
        } catch (\Throwable $e) {
            $this->reverbHealth = [
                'status' => 'error',
                'message' => 'Reverb Service Down',
            ];
        }
    }

    public function render()
    {
        return view('pages.ict.settings.⚡health')->layout('layouts::support');
    }
};
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-base-content">System Health Status</h2>
            <p class="text-xs text-base-content/70 mt-1">Real-time status monitor for database, web server, and Reverb messaging service.</p>
        </div>
        <button wire:click="checkHealth" wire:loading.attr="disabled" class="btn btn-primary text-white text-sm font-semibold shadow">
            <span wire:loading.remove wire:target="checkHealth" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Run Health Check
            </span>
            <span wire:loading wire:target="checkHealth" class="loading loading-spinner loading-xs"></span>
        </button>
    </div>

    <!-- Health Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- 1. Database Health Card -->
        <div class="bg-base-100 rounded-box p-5 border border-base-300 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-btn bg-base-200 text-base-content">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21 3.582 4 8 4s8-1.79 8-4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-base-content">Database Server</h3>
                        <p class="text-xs text-base-content/60 capitalize">{{ $dbHealth['driver'] ?? 'SQL' }} Driver</p>
                    </div>
                </div>
                @if(($dbHealth['status'] ?? '') === 'ok')
                    <span class="badge badge-success text-white font-semibold gap-1.5 py-3 px-3">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                        Operational
                    </span>
                @else
                    <span class="badge badge-error text-white font-semibold gap-1.5 py-3 px-3">
                        Critical
                    </span>
                @endif
            </div>

            <div class="bg-base-200/50 p-3 rounded-btn space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-base-content/70">Response Time:</span>
                    <span class="font-mono font-bold text-base-content">{{ $dbHealth['latency'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/70">Message:</span>
                    <span class="font-medium text-base-content truncate max-w-[200px]">{{ $dbHealth['message'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- 2. HTTP Web Server Card -->
        <div class="bg-base-100 rounded-box p-5 border border-base-300 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-btn bg-base-200 text-base-content">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-base-content">HTTP Web Host</h3>
                        <p class="text-xs text-base-content/60">{{ config('app.url') }}</p>
                    </div>
                </div>
                @if(($httpHealth['status'] ?? '') === 'ok')
                    <span class="badge badge-success text-white font-semibold gap-1.5 py-3 px-3">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                        Active
                    </span>
                @else
                    <span class="badge badge-error text-white font-semibold gap-1.5 py-3 px-3">
                        Unreachable
                    </span>
                @endif
            </div>

            <div class="bg-base-200/50 p-3 rounded-btn space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-base-content/70">HTTP Code:</span>
                    <span class="font-mono font-bold text-base-content">{{ $httpHealth['code'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/70">Message:</span>
                    <span class="font-medium text-base-content">{{ $httpHealth['message'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- 3. Laravel Reverb WebSocket Card -->
        <div class="bg-base-100 rounded-box p-5 border border-base-300 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-btn bg-base-200 text-base-content">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-base-content">Reverb WebSockets</h3>
                        <p class="text-xs text-base-content/60">Realtime Broadcast Engine</p>
                    </div>
                </div>
                @if(($reverbHealth['status'] ?? '') === 'ok')
                    <span class="badge badge-success text-white font-semibold gap-1.5 py-3 px-3">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                        Online
                    </span>
                @else
                    <span class="badge badge-error text-white font-semibold gap-1.5 py-3 px-3">
                        Offline
                    </span>
                @endif
            </div>

            <div class="bg-base-200/50 p-3 rounded-btn space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-base-content/70">Service Status:</span>
                    <span class="font-medium text-base-content">{{ $reverbHealth['message'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- 4. Environment & OS Card -->
        <div class="bg-base-100 rounded-box p-5 border border-base-300 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-btn bg-base-200 text-base-content">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-base-content">System Environment</h3>
                        <p class="text-xs text-base-content/60">Host Machine Details</p>
                    </div>
                </div>
                <span class="badge badge-neutral text-white font-semibold py-3 px-3">
                    PHP {{ PHP_VERSION }}
                </span>
            </div>

            <div class="bg-base-200/50 p-3 rounded-btn space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-base-content/70">Operating System:</span>
                    <span class="font-mono font-bold text-base-content truncate max-w-[200px]">{{ $operatingSystem }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/70">Environment:</span>
                    <span class="font-medium text-base-content uppercase">{{ app()->environment() }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
