<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EthicsApprovalStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
}
