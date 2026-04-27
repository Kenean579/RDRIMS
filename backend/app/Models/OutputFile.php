<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutputFile extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = ['output_id', 'file_id'];

    public function output()
    {
        return $this->belongsTo(Output::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
