<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventStatus extends Model
{
    protected $table = 'event_statuses';
    protected $fillable = ['name', 'label'];
}
