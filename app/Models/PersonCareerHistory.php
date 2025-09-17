<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonCareerHistory extends Model
{
    use HasFactory;

    // Bu model uchun jadvallarni ko'rsating
    protected $table = 'person_career_history';

    // Jadvaldagi birlamchi kalitni ko'rsating
    protected $primaryKey = 'staff_code';

    // Massoviy tayinlash uchun ruxsat berilgan maydonlar
    protected $fillable = [
        'staff_code',
        'id',
        'industry_type_code',
        'job_type_code',
        'job_type__detail',
        'working_type_code',
        'company_name',
        'company_name__f',
        'entry_day',
        'retire_day',
        'period',
        'business_detail',
        'retire_reason',
        'capital',
        'number_employees',
        'summary',
        'entry_note',
        'retire_note',
        'created_atday',
        'update_at',
        'yearly_income',
    ];

    // Tiplar uchun o'zgartirishlar
    protected $casts = [
        'entry_day' => 'datetime',
        'retire_day' => 'datetime',
        'created_atday' => 'datetime',
        'update_at' => 'datetime',
        'period' => 'float',
        'capital' => 'float',
        'number_employees' => 'integer',
        'yearly_income' => 'integer',
    ];
}
