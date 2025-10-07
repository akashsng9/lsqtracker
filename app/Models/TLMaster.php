<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TLMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tbl_tl_master';

    protected $primaryKey = 'id';

    public $timestamps = true;

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'updated_on';

    protected $fillable = [
        'tl_name',
        'contact',
        'email',
        'location',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_on' => 'datetime',
        'updated_on' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
