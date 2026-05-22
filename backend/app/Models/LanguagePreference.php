<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LanguagePreference extends Model
{
    use HasFactory, \App\Traits\HasDynamicStatus;

    public $statusModelMapping = \App\Models\Locale::class;

    protected $fillable = ['user_id', 'locale_id'];

    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
