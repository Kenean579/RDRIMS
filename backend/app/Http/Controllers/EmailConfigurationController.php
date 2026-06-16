<?php

namespace App\Http\Controllers;

use App\Models\EmailConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmailConfigurationController extends Controller
{
    public function show(): JsonResponse
    {
        $config = EmailConfiguration::first();
        if (!$config) {
            // Default to .env if DB is empty
            $config = new EmailConfiguration([
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'password' => config('mail.mailers.smtp.password'),
                'encryption' => config('mail.mailers.smtp.encryption', 'tls'),
                'is_enabled' => true,
            ]);
        }
        
        // Hide password in response
        $config->makeHidden('password');

        return response()->json($config);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string',
            'is_enabled' => 'boolean',
        ]);

        $config = EmailConfiguration::first() ?? new EmailConfiguration();
        
        $config->fill($validated);
        
        if ($request->has('password') && !empty($request->password)) {
            $config->password = $request->password; // Will be casted to encrypted
        }

        $config->save();

        // Hide password in response
        $config->makeHidden('password');

        return response()->json([
            'message' => 'Email configuration updated successfully',
            'config' => $config
        ]);
    }

    public function testEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $config = EmailConfiguration::first();
        // Use env defaults if no config stored
        if (!$config) {
            $config = new EmailConfiguration([
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'password' => config('mail.mailers.smtp.password'),
                'encryption' => config('mail.mailers.smtp.encryption', 'tls'),
                'is_enabled' => config('mail.mailers.smtp.enabled', false),
            ]);
        }

        if (!$config->is_enabled) {
            return response()->json(['message' => 'SMTP is disabled. Enable it in Email Configuration first.'], 400);
        }

        // Dynamically override SMTP settings for this request
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $config->host);
        Config::set('mail.mailers.smtp.port', $config->port);
        Config::set('mail.mailers.smtp.username', $config->username);
        Config::set('mail.mailers.smtp.password', $config->password);
        Config::set('mail.mailers.smtp.encryption', $config->encryption);
        Config::set('mail.from.address', $config->sender_address ?? config('mail.from.address'));
        Config::set('mail.from.name', $config->sender_name ?? config('mail.from.name'));

        try {
            Mail::raw('This is a test email from RDRIMS to verify SMTP configuration.', function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('RDRIMS - SMTP Test Email');
            });

            $config->update(['last_tested_at' => now()]);
            return response()->json(['message' => 'Test email sent successfully']);
        } catch (\Exception $e) {
            // Provide clearer guidance for authentication failures
            $msg = $e->getMessage();
            if (strpos($msg, '535') !== false) {
                $msg = 'Authentication failed: please check your SMTP username/password. If using Gmail, ensure App Passwords or “Allow less secure apps” is enabled.';
            }
            return response()->json(['message' => 'Failed to send test email: ' . $msg], 500);
        }
    }
}
