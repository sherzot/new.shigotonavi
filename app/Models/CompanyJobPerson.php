<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PersonHopeJobType;
use App\Models\PersonHopeWorkingPlace;
use App\Models\PersonHopeWorkingCondition;

class CompanyJobPerson extends Model
{
    use HasFactory;

    protected $table = 'company_job_person';
    public $timestamps = false;

    protected $fillable = [
        'person_code',
        'order_code',
        'company_code',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->person_code) {
                $lastPerson = self::max('person_code') ?? 0;
                $model->person_code = intval($lastPerson) + 1;
            }

            if (!$model->order_code) {
                $lastOrder = self::orderByRaw('CAST(SUBSTRING(order_code, 2) AS UNSIGNED) DESC')->first();
                $nextOrder = $lastOrder ? intval(substr($lastOrder->order_code, 1)) + 1 : 1;
                $model->order_code = 'J' . str_pad($nextOrder, 7, '0', STR_PAD_LEFT);
            }

            if (!$model->company_code) {
                $lastCompany = self::orderByRaw('CAST(SUBSTRING(company_code, 2) AS UNSIGNED) DESC')->first();
                $nextCompany = $lastCompany ? intval(substr($lastCompany->company_code, 1)) + 1 : 1;
                $model->company_code = 'C' . str_pad($nextCompany, 7, '0', STR_PAD_LEFT);
            }
        });
    }



    // CompanyJobPerson model
    public function companyPerson()
    {
        return $this->belongsTo(CompanyPerson::class, 'person_code', 'person_code');
    }



}
