<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'database' => $this->checkDatabase(),
            'storage'  => $this->checkStorage(),
            'queue'    => $this->checkQueue(),
            'email'    => $this->checkEmail(),
        ]);
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Connection failed'];
        }
    }

    private function checkStorage(): array
    {
        try {
            $testFile = 'health_check.txt';
            Storage::disk('public')->put($testFile, 'ok');
            Storage::disk('public')->delete($testFile);
            return ['status' => 'ok', 'message' => 'Writable'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Not writable'];
        }
    }

    private function checkQueue(): array
    {
        // Simple check: is the connection configured?
        $connection = config('queue.default');
        return ['status' => 'ok', 'message' => "Configured ($connection)"];
    }

    private function checkEmail(): array
    {
        $mailer = config('mail.default');
        if ($mailer === 'log' || $mailer === 'array') {
            return ['status' => 'warning', 'message' => "Using $mailer driver (Development)"];
        }

        try {
            $transport = Mail::getSymfonyTransport();
            // We can't strictly ping SMTP transport without trying to connect.
            // Some drivers don't support ping.
            if (method_exists($transport, 'start')) {
                $transport->start();
                $transport->stop();
            }
            return ['status' => 'ok', 'message' => 'SMTP configured'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'SMTP connection failed: ' . $e->getMessage()];
        }
    }
}
