<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonHopeJobType extends Model
{
    use HasFactory;

    protected $table = 'person_hope_job_type';
    protected $primaryKey = 'staff_code';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'staff_code',
        'id',
        'job_type_code',
        'job_type_detail',
        'created_at',
        'update_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'update_at' => 'datetime',
    ];
}

