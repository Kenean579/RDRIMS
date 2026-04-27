<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'patent_id',
        'company_name',
        'start_date',
        'end_date',
        'royalty_rate'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'royalty_rate' => 'decimal:2'
        ];
    }

    public function patent()
    {
        return $this->belongsTo(Patent::class);
    }
}
