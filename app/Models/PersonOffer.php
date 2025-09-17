<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonOffer extends Model
{
    use HasFactory;

    protected $table = 'person_offer';
    // 主キーの指定
    protected $primaryKey = 'staff_code';
    // 主キーが自動インクリメントされない場合:
    public $incrementing = false;

     // 主キーのデータ型
     protected $keyType = 'string';
     
    protected $fillable = [
        'staff_code',
        'order_code',^
        'company_code',
        'agent_code',
        'created_at',
        'update_at'
    ];

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'update_at';

    protected static function boot()
    {

        parent::boot();

        static::updating(function ($personOffer) {
            $personOffer->update_at = now();
        });
    }


}
