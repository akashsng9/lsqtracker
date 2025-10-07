<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $primaryKey = 'ProspectID';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'leads';
    protected $guarded = [];
}
