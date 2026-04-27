<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatentStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
}
