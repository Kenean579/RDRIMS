<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = ['project_id', 'file_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
