<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HierarchicalScope;

class AuditLog extends Model
{
    use HasFactory, HierarchicalScope;

    public $timestamps = false; // only created_at exists

    protected $fillable = [
        'user_id', 
        'university_id', 'campus_id', 'faculty_id', 'department_id', 'research_center_id',
        'action', 'table_name', 'record_id', 
        'old_values', 'new_values', 'ip_address', 'created_at'
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit log entry with automatic hierarchical scoping.
     */
    public static function record($action, $table, $id, $old = null, $new = null)
    {
        $user = auth()->user();
        if (!$user) return null;

        return self::create([
            'user_id' => $user->id,
            'university_id' => $user->university_id,
            'campus_id' => $user->campus_id,
            'faculty_id' => $user->faculty_id,
            'department_id' => $user->department_id,
            'research_center_id' => $user->research_center_id,
            'action' => $action,
            'table_name' => $table,
            'record_id' => $id,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);
    }
}
