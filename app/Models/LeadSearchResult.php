<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadSearchResult extends Model
{
    use HasFactory;
    protected $table = 'lead_search_results';
    protected $guarded = [];
}
