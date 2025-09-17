<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonHopeWorkingPlace extends Model
{
    use HasFactory;

    protected $table = 'person_hope_working_place';
    public $timestamps = false;

    protected $fillable = ['id', 'staff_code', 'prefecture_code', 'city', 'area', 'created_at', 'update_at'];

    protected $casts = [
        'created_at' => 'datetime',
        'update_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Staff_code に基づいて最大 ID 値を取得する
            $maxId = self::where('staff_code', $model->staff_code)->max('id');
            $model->id = $maxId ? $maxId + 1 : 1; // 次の値がある場合はそれを指定します
        });
    }

}


