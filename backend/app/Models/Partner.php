<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sector',
        'contact_email',
        'website'
    ];

    public function moUs()
    {
        return $this->hasMany(MoU::class);
    }

    public function outputs()
    {
        return $this->hasMany(Output::class);
    }
}
