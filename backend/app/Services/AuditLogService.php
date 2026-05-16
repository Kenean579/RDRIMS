<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(string $action, string $tableName, int $recordId, ?Request $request = null): void
    {
        AuditLog::create([
            'user_id' => $request?->user()?->id,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'ip_address' => $request?->ip(),
            'created_at' => now(),
        ]);
    }
}
