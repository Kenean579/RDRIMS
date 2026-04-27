<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'abstract',
        'keywords',
        'journal',
        'doi',
        'scholar_url',
        'publication_date',
        'citation_count',
        'file_id'
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'citation_count' => 'integer'
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function authors()
    {
        return $this->belongsToMany(User::class, 'publication_authors')
                    ->withPivot(['external_author_name', 'external_institution', 'author_order']);
    }
}
