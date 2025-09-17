<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonUserInfo extends Model
{
    use HasFactory;

    protected $table = 'person_userinfo';
    // 主キーの指定
    protected $primaryKey = 'staff_code';
    // 主キーが自動インクリメントされない場合:
    public $incrementing = false;

     // 主キーのデータ型
     protected $keyType = 'string';


    protected $fillable = [
        'staff_code',
        'regist_commit',
        'password',
        'access_count',
        'operate_class_com_code',
        'operate_class_web_code',
        'operate_class_remark',
        'branch_code',
        'employee_code',
        'temp_flag',
        'introduction_flag',
        'temp_to_perm_flag',
        'mail_magazine_flag',
        'new_joho_mail_flag',
        'suspension_flag',
        'erasure_flag',
        'offer_flag',
        'hope_use_flag',
        'navi_use_flag',
        'home_contact_flag',
        'portable_contact_flag',
        'fax_contact_flag',
        'mail_contact_flag',
        'refer_reject_flag',
        'person_danger_flag',
        'priority_job_type_flag',
        'priority_industry_type_flag',
        'priority_working_type_flag',
        'priority_working_place_flag',
        'priority_station_flag',
        'priority_working_time_flag',
        'priority_salary_flag',
        'hope_commute_time',
        'notice_mail_flag',
        'last_access_day',
        'lis_reserve_day',
        'lis_regist_day',
        'created_at',
        'update_at',
        'jms_suspension_flag',
    ];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'update_at';

    protected $hidden = [
        'password', // パスワードを非表示にする
    ];


    protected static function boot()
    {

        parent::boot();

        static::updating(function ($personInfo) {
            $personInfo->update_at = now();
        });
    }

}
