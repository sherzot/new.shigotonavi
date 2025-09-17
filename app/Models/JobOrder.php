<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PersonHopeJobType;
use App\Models\PersonHopeWorkingPlace;
use App\Models\PersonHopeWorkingCondition;

class JobOrder extends Model
{
    use HasFactory;

    protected $table = 'job_order';
    protected $primaryKey = 'order_code'; // Primary key ustuni
    public $incrementing = false; // Primary key avtomatik oshmaydi
    public $timestamps = true; // Jadvalda vaqt maydonlari yo'q

    protected $fillable = [
        'order_code',
        'company_code',
        'regist_commit',
        'public_flag',
        'public_day',
        'public_limit_day',
        'recruitment_limit_day',
        'competition_flag',
        'competition_remark',
        'client_class_flag',
        'client_class_remark',
        'order_condition_flag',
        'order_condition_remark',
        'access_count',
        'order_type',
        'order_progress_type',
        'branch_code',
        'employee_code',
        'coordinator_code',
        'job_type_detail',
        'business_detail',
        'hope_school_history_code',
        'age_min',
        'age_max',
        'age_reason_flag',
        'age_min2_best',
        'age_max2_best',
        'yearly_income_min',
        'yearly_income_max',
        'monthly_income_min',
        'monthly_income_max',
        'daily_income_min',
        'daily_income_max',
        'hourly_income_min',
        'hourly_income_max',
        'percentage_pay_flag',
        'income_remark',
        'experienced_flag1',
        'experienced_industry_type_code1',
        'experienced_industry_type_period1',
        'experienced_job_type_code1',
        'experienced_job_type_period1',
        'age_min2',
        'age_max2',
        'yearly_income_min2',
        'yearly_income_max2',
        'monthly_income_min2',
        'monthly_income_max2',
        'daily_income_min2',
        'daily_income_max2',
        'hourly_income_min2',
        'hourly_income_max2',
        'percentage_pay_flag2',
        'income_remark2',
        'experienced_flag2',
        'experienced_industry_type_code2',
        'experienced_industry_type_period2',
        'experienced_job_type_code2',
        'experienced_job_type_period2',
        'allowance',
        'work_time_remark',
        'weekly_holiday_type',
        'holiday_remark',
        'uniform_flag',
        'uniform_size',
        'locker_flag',
        'employee_restaurant_flag',
        'board_flag',
        'smoking_flag',
        'smoking_area_flag',
        'duty_system_flag',
        'duty_type',
        'duty_time_flag',
        'transfer_flag',
        'over_work_flag',
        'foreign_flag',
        'working_place_companyname',
        'created_at',
        'updated_at',
        'jms_image_id',
    ];
    public function hopeJobType()
    {
        return $this->hasMany(PersonHopeJobType::class, 'staff_code', 'staff_code');
    }

}
