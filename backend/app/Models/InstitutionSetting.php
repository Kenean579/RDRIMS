<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'value', 'description',
        'university_id', 'campus_id', 'faculty_id', 'department_id', 'research_center_id',
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function researchCenter(): BelongsTo
    {
        return $this->belongsTo(ResearchCenter::class);
    }

    /**
     * Get the effective setting value for a given hierarchy level.
     * Resolves from most specific (department) to most general (university).
     */
    public static function getEffective(string $key, ?int $universityId = null, ?int $campusId = null, ?int $facultyId = null, ?int $departmentId = null, ?int $researchCenterId = null): ?string
    {
        // Priority: research_center > department > faculty > campus > university
        $levels = [
            ['research_center_id' => $researchCenterId],
            ['department_id' => $departmentId],
            ['faculty_id' => $facultyId],
            ['campus_id' => $campusId],
            ['university_id' => $universityId],
        ];

        foreach ($levels as $level) {
            $setting = self::where('key', $key)
                ->where($level)
                ->first();
            if ($setting) {
                return $setting->value;
            }
        }

        return null;
    }
}
