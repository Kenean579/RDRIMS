<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceCheck extends Model
{
    use HasFactory;

    protected $fillable = ['proposal_id', 'checker_id', 'status_id', 'comments', 'checked_at'];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function status()
    {
        return $this->belongsTo(FinanceCheckStatus::class, 'status_id');
    }
}
