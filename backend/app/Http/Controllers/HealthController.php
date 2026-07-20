<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class HealthController extends Controller
{
    /**
     * Public ping endpoint for Load Balancers and Uptime Monitors.
     * Fast, lightweight, and determines if the app can serve requests.
     * Exposes NO sensitive configuration details.
     */
    public function ping(): JsonResponse
    {
        $db = $this->checkDatabase();
        
        $status = $db['status'] === 'ok' ? 'ok' : 'down';
        $httpCode = $status === 'ok' ? 200 : 503;

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
        ], $httpCode);
    }

    /**
     * Detailed administrative health check.
     * Protected by super_admin middleware.
     */
    public function index(): JsonResponse
    {
        $database = $this->checkDatabase();
        $storage = $this->checkStorage();
        $queue = $this->checkQueue();
        $email = $this->checkEmail();

        // Determine overall status and HTTP Code
        // If DB or Storage fails, it's a critical failure (503).
        // If Email fails, it's degraded (200).
        $isCriticalFailure = $database['status'] !== 'ok' || $storage['status'] !== 'ok';
        $isDegraded = $queue['status'] === 'warning' || $email['status'] === 'error';

        if ($isCriticalFailure) {
            $overallStatus = 'down';
            $httpCode = 503;
        } elseif ($isDegraded) {
            $overallStatus = 'degraded';
            $httpCode = 200;
        } else {
            $overallStatus = 'healthy';
            $httpCode = 200;
        }

        return response()->json([
            'status'      => $overallStatus,
            'timestamp'   => now()->toIso8601String(),
            'environment' => app()->environment(),
            'version'     => [
                'php'     => PHP_VERSION,
                'laravel' => app()->version(),
            ],
            'metrics'     => [
                'disk_usage_percent' => $this->getDiskUsagePercent(),
                'memory_usage_mb'    => round(memory_get_usage(true) / 1024 / 1024, 2),
            ],
            // Core component dependencies
            'database' => $database,
            'storage'  => $storage,
            'queue'    => $queue,
            'email'    => $email,
        ], $httpCode);
    }

    private function measureLatency(callable $callback): array
    {
        $start = microtime(true);
        try {
            $result = $callback();
            $latency = round((microtime(true) - $start) * 1000, 2);
            return array_merge(['status' => 'ok'], $result, ['latency_ms' => $latency]);
        } catch (\Exception $e) {
            $latency = round((microtime(true) - $start) * 1000, 2);
            return [
                'status'     => 'error',
                'message'    => $e->getMessage(),
                'latency_ms' => $latency
            ];
        }
    }

    private function checkDatabase(): array
    {
        return $this->measureLatency(function () {
            DB::connection()->getPdo();
            return ['message' => 'Connected'];
        });
    }

    private function checkStorage(): array
    {
        return $this->measureLatency(function () {
            $testFile = 'health/health_check_' . uniqid() . '.txt';
            Storage::disk('public')->put($testFile, 'ok');
            Storage::disk('public')->delete($testFile);
            return ['message' => 'Writable'];
        });
    }

    private function checkQueue(): array
    {
        return $this->measureLatency(function () {
            $connection = config('queue.default');
            // Basic check to see if queue connection config exists
            if (empty($connection) || $connection === 'sync') {
                return ['status' => 'warning', 'message' => "Configured ($connection)"];
            }
            return ['message' => "Configured ($connection)"];
        });
    }

    private function checkEmail(): array
    {
        $start = microtime(true);
        $mailer = config('mail.default');
        
        if ($mailer === 'log' || $mailer === 'array') {
            return [
                'status'     => 'warning', 
                'message'    => "Using $mailer driver (Development)",
                'latency_ms' => 0
            ];
        }

        try {
            if ($mailer === 'smtp') {
                $host = config('mail.mailers.smtp.host', '127.0.0.1');
                $port = config('mail.mailers.smtp.port', 2525);
                
                // Professional fix: use a fast TCP socket check with a strict 2-second timeout 
                // to prevent single-threaded servers (like artisan serve) from hanging.
                $fp = @fsockopen($host, $port, $errno, $errstr, 2);
                if (!$fp) {
                    throw new \Exception("SMTP connection refused ($errstr)");
                }
                fclose($fp);
            }

            $latency = round((microtime(true) - $start) * 1000, 2);
            return [
                'status'     => 'ok', 
                'message'    => 'SMTP configured and reachable',
                'latency_ms' => $latency
            ];
        } catch (\Exception $e) {
            $latency = round((microtime(true) - $start) * 1000, 2);
            return [
                'status'     => 'error', 
                'message'    => 'SMTP connection failed: ' . $e->getMessage(),
                'latency_ms' => $latency
            ];
        }
    }

    private function getDiskUsagePercent(): ?float
    {
        try {
            $path = base_path();
            $free = disk_free_space($path);
            $total = disk_total_space($path);
            if ($total === false || $total == 0) return null;
            $used = $total - $free;
            return round(($used / $total) * 100, 2);
        } catch (\Exception $e) {
            return null;
        }
    }
}
