<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PersonHopeJobType;
use App\Models\PersonHopeWorkingPlace;
use App\Models\PersonHopeWorkingCondition;

class CompanyPerson extends Model
{
    use HasFactory;

    protected $table = 'company_person';
    protected $primaryKey = 'person_code'; // Primary key ustuni
    public $timestamps = false; // Jadvalda vaqt maydonlari yo'q

    protected $fillable = [
        'person_code',
        'person_name',
        'position',
        'mail_address',
        'tel',
        'fax',
        'company_name',
        'section_name',
        'post',
        'prefecture_code',
        'city',
        'town',
        'address',
        'active_flag',
        'created_at',
        'update_at',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $lastPerson = self::orderByRaw('CAST(SUBSTRING(person_code, 2) AS UNSIGNED) DESC')->first();
            $nextCode = $lastPerson ? intval(substr($lastPerson->person_code, 1)) + 1 : 1;
            $model->person_code = 'C' . str_pad($nextCode, 7, '0', STR_PAD_LEFT);
        });
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
