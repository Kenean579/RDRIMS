<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuditRequests extends Command
{
    protected $signature = 'audit:requests';

    protected $description = 'Audit FormRequests against database schema';

    public function handle()
    {
        $requestsPath = app_path('Http/Requests');
        $files = File::allFiles($requestsPath);

        $this->info('Auditing Form Requests...');

        $discrepanciesCount = 0;

        foreach ($files as $file) {
            $className = 'App\\Http\\Requests\\'.str_replace('/', '\\', $file->getRelativePathname());
            $className = str_replace('.php', '', $className);

            if (! class_exists($className)) {
                continue;
            }

            $reflection = new \ReflectionClass($className);
            if (! $reflection->isInstantiable() || ! $reflection->hasMethod('rules')) {
                continue;
            }

            $requestName = class_basename($className);

            // Try to extract model name from StoreXRequest or UpdateXRequest
            $modelName = null;
            if (Str::startsWith($requestName, 'Store')) {
                $modelName = str_replace(['Store', 'Request'], '', $requestName);
            } elseif (Str::startsWith($requestName, 'Update')) {
                $modelName = str_replace(['Update', 'Request'], '', $requestName);
            } else {
                continue; // Skip specialized requests for now
            }

            $modelClass = 'App\\Models\\'.$modelName;
            if (! class_exists($modelClass)) {
                continue;
            }

            try {
                $model = new $modelClass;
                $table = $model->getTable();

                // Exclude system columns
                $dbColumns = array_diff(Schema::getColumnListing($table), [
                    'id', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at', 'remember_token', 'password',
                ]);

                // Exclude columns handled by controllers
                $ignoredColumns = ['created_by', 'submitted_by', 'approved_by', 'assigned_by', 'requested_by', 'pi_id', 'status_id', 'submitted_at', 'approved_at', 'checked_at', 'invited_at', 'assigned_at', 'claimed_at', 'completed_at', 'generated_at', 'read_at'];
                $expectedColumns = array_diff($dbColumns, $ignoredColumns);

                // Instantiate request to get rules
                $requestInstance = new $className;
                $rules = $requestInstance->rules();
                $validatedKeys = array_keys($rules);

                $missingInRules = array_diff($expectedColumns, $validatedKeys);
                $extraInRules = array_diff($validatedKeys, $dbColumns);

                // Filter out standard fields that might be in rules but not db
                $extraInRules = array_filter($extraInRules, function ($field) {
                    return ! in_array($field, ['password_confirmation', 'current_password', 'roles', 'permissions']);
                });

                if (count($missingInRules) > 0 || count($extraInRules) > 0) {
                    $this->warn("\n[!] {$requestName} (Table: {$table})");

                    if (count($missingInRules) > 0) {
                        $this->line('  Missing from rules: <fg=red>'.implode(', ', $missingInRules).'</>');
                    }
                    if (count($extraInRules) > 0) {
                        $this->line('  Extra in rules (not in DB): <fg=yellow>'.implode(', ', $extraInRules).'</>');
                    }
                    $discrepanciesCount++;
                }
            } catch (\Exception $e) {
                // Ignore instantiation errors for complex requests
            }
        }

        $this->info("\nAudit complete. Found discrepancies in {$discrepanciesCount} files.");
    }
}
