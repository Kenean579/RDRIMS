<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgreementFile extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = ['parent_type_id', 'parent_id', 'file_id'];

    public function agreementType()
    {
        return $this->belongsTo(AgreementType::class, 'parent_type_id');
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
