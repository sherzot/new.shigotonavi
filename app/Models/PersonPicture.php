<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersonPicture extends Model
{
        use HasFactory;

    protected $table = 'person_picture';

    protected $fillable = ['staff_code', 'picture' , 'created_at', 'update_at'];

    public $timestamps = false; //true;
}
