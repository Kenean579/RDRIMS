<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundingStatus extends Model
{
    protected $table = 'funding_statuses';
    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    public function fundings(): HasMany
    {
        return $this->hasMany(Funding::class, 'status_id');
    }
}
