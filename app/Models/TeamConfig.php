<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'ProspectID',
        'ProspectAutoId',
        'EmailAddress',
        'Score',
        'OwnerId',
        'IsStarredLead',
        'CanUpdate',
        'IsTaggedLead',
        'raw',
        'Total'
    ];

    protected $casts = [
        'IsStarredLead' => 'boolean',
        'IsTaggedLead'  => 'boolean',
        'CanUpdate'     => 'boolean',
    ];
}
