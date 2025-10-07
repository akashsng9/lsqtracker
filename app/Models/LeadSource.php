<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    use HasFactory;

    protected $table = 'tbl_lead_source_master';
    
    protected $fillable = [
        'leadSource',
        'sourceType',
        'type',
        'status'
    ];

    public $timestamps = false;
    
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'createdAt';
}
