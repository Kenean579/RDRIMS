<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sector', 'contact_email', 'website'];

    public function moUs(): HasMany
    {
        return $this->hasMany(MoU::class);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(Output::class);
    }
}
