<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id', 'type_id', 'title', 'abstract', 'objectives', 'methodology',
        'keywords', 'budget', 'budget_allocation', 'status_change_comment',
        'status_id', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at',
        'academic_year_id', 'file_id', 'ethics_file_id', 'ethics_approval_status_id'
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'budget_allocation' => 'json',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function call()
    {
        return $this->belongsTo(Call::class);
    }

    public function type()
    {
        return $this->belongsTo(ProposalType::class, 'type_id');
    }

    public function status()
    {
        return $this->belongsTo(ProposalStatus::class, 'status_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function ethicsFile()
    {
        return $this->belongsTo(File::class, 'ethics_file_id');
    }

    public function ethicsApprovalStatus()
    {
        return $this->belongsTo(EthicsApprovalStatus::class, 'ethics_approval_status_id');
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }

    public function investigators()
    {
        return $this->hasMany(ProposalInvestigator::class);
    }

    public function reviewers()
    {
        return $this->hasMany(ProposalReviewer::class);
    }

    public function financeChecks()
    {
        return $this->hasMany(FinanceCheck::class);
    }

    public function ethicsRequests()
    {
        return $this->hasMany(EthicsRequest::class);
    }
}
