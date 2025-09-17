<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterPerson extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable, HasFactory;

    protected $table = 'master_person';
    protected $primaryKey = 'staff_code';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'staff_code', 'mail_address', 'verification_token', 'verified_at', 'regist_commit',
        'name', 'name_f', 'search_name', 'sear_chname_f', 'oldname', 'birthday', 'age',
        'sex', 'marriage_flag', 'post_u', 'post_l', 'prefecture_code', 'city', 'city_f',
        'town', 'town_f', 'address', 'address_f', 'living_type', 'home_telephone_number',
        'country_telephonenumber', 'portable_telephone_number', 'fax_number',
        'portable_mail_address', 'urgency_post_u', 'urgency_post_l', 'urgency_address',
        'urgency_address_f', 'urgency_telephone_number', 'url', 'info_source_type',
        'info_source_day', 'info_source_other', 'dependent_flag', 'dependent_number',
        'spouse_flag', 'current_company_name', 'current_company_name_f',
        'society_insurance_in', 'society_insurance_loss', 'employ_insurance_in',
        'employ_insurance_loss', 'avatar_image_display_flag', 'jms_image_id', 'created_at', 'updated_at',
    ];

    protected $hidden = [
        'verification_token', 'search_name', 'password',
    ];

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

    protected static function boot()
    {
        parent::boot();

        // Har bir yangi yaratilgan foydalanuvchi uchun post kodni bo'lish
        static::creating(function ($person) {
            if (!empty($person->postal_code)) {
                $postalCode = str_replace('-', '', $person->postal_code);
                $person->post_u = substr($postalCode, 0, 3);
                $person->post_l = substr($postalCode, 3);
            }
        });
    }
}
