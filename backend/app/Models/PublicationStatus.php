<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicationStatus extends Model
{
    protected $table = 'publication_statuses';
    protected $fillable = ['name', 'label'];
}
