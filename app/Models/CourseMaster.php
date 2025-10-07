<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMaster extends Model
{
    use HasFactory;

    protected $table = 'tbl_course_master';

    protected $primaryKey = 'id';

    public $timestamps = true; // uses created_at and updated_at

    protected $fillable = [
        'courseName',
        'CourseId',
        'courseLocation',
        'CourseStatus',
        'keyword',
    ];

    protected $casts = [
        'CourseStatus' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
