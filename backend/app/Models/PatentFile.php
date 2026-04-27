<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatentFile extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = ['patent_id', 'file_id'];

    public function patent()
    {
        return $this->belongsTo(Patent::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
