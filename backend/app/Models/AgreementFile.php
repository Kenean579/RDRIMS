<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgreementFile extends Model
{
    use HasFactory;

    protected $fillable = ['parent_type_id', 'parent_id', 'file_id'];

    public function parentType(): BelongsTo
    {
        return $this->belongsTo(AgreementType::class, 'parent_type_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
