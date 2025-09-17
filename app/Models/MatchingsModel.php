<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PersonHopeJobType;
use App\Models\PersonHopeWorkingPlace;
use App\Models\PersonHopeWorkingCondition;

class MatchingsModel extends Model
{
    use HasFactory;

    protected $table = 'matchings';

    protected $fillable = [
        'gender',
        'birth_date',
        'postal_code',
        'surname',
        'name',
        'katakana_surname',
        'katakana_name',
        'additional_field',
        'phone_number',
        'desired_job',
        'job_category',
        'job_detail',
        'specialization',
        'desired_salary_type',
        'desired_salary_annual',
        'desired_salary_hourly',
        'staff_code',
        'company_id'
    ];
    public function masterPerson()
    {
        return $this->belongsTo(MasterPerson::class, 'staff_code', 'staff_code');
    }

    public function matchings()
    {
        return $this->hasMany(self::class, 'staff_code', 'staff_code');
    }
    public function hopeWorkingCondition()
    {
        return $this->hasOne(PersonHopeWorkingCondition::class, 'staff_code', 'staff_code');
    }

    public function hopeWorkingPlace()
    {
        return $this->hasOne(PersonHopeWorkingPlace::class, 'staff_code', 'staff_code');
    }

    public function hopeJobType()
    {
        return $this->hasMany(PersonHopeJobType::class, 'staff_code', 'staff_code');
    }


}



