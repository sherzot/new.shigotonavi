<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonHopeWorkingCondition extends Model
{
    use HasFactory;

    protected $table = 'person_hope_working_condition';
    protected $primaryKey = 'staff_code';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'staff_code',
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
        'traffic_fee_flag',
        'society_insurance_flag',
        'sanatorium_flag',
        'enterprise_pension_flag',
        'wealth_shape_flag',
        'stock_option_flag',
        'retirement_pay_flag',
        'residence_pay_flag',
        'family_pay_flag',
        'employee_dormitory_flag',
        'company_house_flag',
        'new_employee_training_flag',
        'overseas_training_flag',
        'other_training_flag',
        'flex_time_flag',
        'work_period_flag',
        'work_month_period',
        'work_start_time',
        'work_end_time',
        'work_shift_flag',
        'over_work_flag',
        'over_work_time_max',
        'over_work_time_other',
        'mon_holiday_flag',
        'tue_holiday_flag',
        'wed_holiday_flag',
        'thu_holiday_flag',
        'fri_holiday_flag',
        'sat_holiday_flag',
        'sun_holiday_flag',
        'public_holiday_flag',
        'weekly_holiday_type',
        'holiday_remark',
        'transfer_flag',
        'hope_work_start_day',
        'regist_day',
        'update_day',
        'many_holiday_flag',
        'weekend_holiday_flag',
        'no_smoking_flag',
        'handicapped_flag',
        'maternity_flag',
        'mammy_flag',
        'rent_all_flag',
        'rent_part_flag',
        'company_cafeteria_flag',
        'meals_flag',
        'meals_assistance_flag',
        'training_cost_flag',
        'entrepreneur_cost_flag',
        'money_flag',
        'land_shop_flag',
        'find_job_festive_flag',
        'salary_increase_flag',
        'bonus_flag',
        'retirement_flag',
        'reemployment_flag',
        'childcare_leave_flag',
        'nursing_care1_flag',
        'nursing_care2_flag',
        'license_acquisition_support_flag',
        'severance_pay_flag',
        'meritocracy_flag',
        'telework'
    ];
    public static function defaultData()
    {
        return [
            'yearly_income_min' => 0,
            'yearly_income_max' => 0,
            'monthly_income_min' => 0,
            'monthly_income_max' => 0,
            'daily_income_min' => 0,
            'daily_income_max' => 0,
            'hourly_income_min' => 0,
            'hourly_income_max' => 0,
            'percentage_pay_flag' => 0,
            'income_remark' => null,
            'traffic_fee_flag' => 0,
            'society_insurance_flag' => 0,
            'sanatorium_flag' => 0,
            'enterprise_pension_flag' => 0,
            'wealth_shape_flag' => 0,
            'stock_option_flag' => 0,
            'retirement_pay_flag' => 0,
            'residence_pay_flag' => 0,
            'family_pay_flag' => 0,
            'employee_dormitory_flag' => 0,
            'company_house_flag' => 0,
            'new_employee_training_flag' => 0,
            'overseas_training_flag' => 0,
            'other_training_flag' => 0,
            'flex_time_flag' => 0,
            'work_period_flag' => 0,
            'work_month_period' => 0,
            'work_start_time' => null,
            'work_end_time' => null,
            'work_shift_flag' => 0,
            'over_work_flag' => 0,
            'over_work_time_max' => 0,
            'over_work_time_other' => null,
            'mon_holiday_flag' => 0,
            'tue_holiday_flag' => 0,
            'wed_holiday_flag' => 0,
            'thu_holiday_flag' => 0,
            'fri_holiday_flag' => 0,
            'sat_holiday_flag' => 0,
            'sun_holiday_flag' => 0,
            'public_holiday_flag' => 0,
            'weekly_holiday_type' => 0,
            'holiday_remark' => null,
            'transfer_flag' => 0,
            'hope_work_start_day' => now(),
            'regist_day' => now(),
            'update_day' => now(),
            'many_holiday_flag' => 0,
            'weekend_holiday_flag' => 0,
            'no_smoking_flag' => 0,
            'handicapped_flag' => 0,
            'maternity_flag' => 0,
            'mammy_flag' => 0,
            'rent_all_flag' => 0,
            'rent_part_flag' => 0,
            'company_cafeteria_flag' => 0,
            'meals_flag' => 0,
            'meals_assistance_flag' => 0,
            'training_cost_flag' => 0,
            'entrepreneur_cost_flag' => 0,
            'money_flag' => 0,
            'land_shop_flag' => 0,
            'find_job_festive_flag' => 0,
            'salary_increase_flag' => 0,
            'bonus_flag' => 0,
            'retirement_flag' => 0,
            'reemployment_flag' => 0,
            'childcare_leave_flag' => 0,
            'nursing_care1_flag' => 0,
            'nursing_care2_flag' => 0,
            'license_acquisition_support_flag' => 0,
            'severance_pay_flag' => 0,
            'meritocracy_flag' => 0,
            'telework' => 0
        ];
    }
    public static function updateSelectiveOrCreate($staffCode, array $data)
    {
        // ユーザーが存在するか新規か確認する
        $personCondition = self::firstOrNew(['staff_code' => $staffCode]);
    
        // 新規ユーザーの場合は、**すべての列に0またはnullを入力します**
        if (!$personCondition->exists) {
            $personCondition->fill(self::defaultData());
        }
    
        // コントローラーからの列のみを更新します。
        foreach ($data as $key => $value) {
            $personCondition->$key = $value;
        }
    
        // `update_day`を更新します
        $personCondition->hope_work_start_day = now();
        $personCondition->regist_day = now();
        $personCondition->update_day = now();
        $personCondition->save();
    
        return $personCondition;
    }

    // 型変換（casting）
    protected $casts = [
        'hope_work_start_day' => 'datetime',
        'regist_day' => 'datetime',
        'update_day' => 'datetime',
        'created_at' => 'datetime',
        'update_at' => 'datetime',
    ];

    // 起動方法の設定
    protected static function boot()
    {
        parent::boot();

        // `update_at` フィールドの自動更新
        static::updating(function ($person) {
            $person->update_day = now();
        });
    }
}
