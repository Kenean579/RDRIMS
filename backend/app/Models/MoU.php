<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoU extends Model
{
    use HasFactory;

    protected $table = 'mo_u_s';

    protected $fillable = [
        'partner_id',
        'start_date',
        'end_date'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date'
        ];
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
