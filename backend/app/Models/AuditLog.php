<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    // The schema specifies no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'ip_address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
