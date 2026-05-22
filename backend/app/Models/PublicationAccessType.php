<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicationAccessType extends Model
{
    protected $table = 'publication_access_types';
    protected $fillable = ['name', 'label'];
}
