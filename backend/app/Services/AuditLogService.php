<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(string $action, string $tableName, int $recordId, ?Request $request = null): void
    {
        AuditLog::record($action, $tableName, $recordId);
    }
}
