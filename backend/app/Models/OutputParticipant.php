<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutputParticipant extends Model
{
    use HasFactory;

    protected $fillable = ['output_id', 'user_id', 'participant_type_id'];

    public function output()
    {
        return $this->belongsTo(Output::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participantType()
    {
        return $this->belongsTo(ParticipantType::class, 'participant_type_id');
    }
}
