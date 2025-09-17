<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class CustomCompanyUser extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'master_company';
    public $timestamps = false;
    // 一括割り当てのフィールド
    protected $fillable = [
        'company_code',
        'temp_code',
        'password',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    // 必要に応じて、次のメソッドを書き換えることができます
    public function getAuthIdentifierName()
    {
        return 'company_code';
    }

    public function getAuthIdentifier()
    {
        return $this->attributes['company_code'] ?? null;
    }

    public function getAuthPassword()
    {
        return $this->attributes['password'] ?? null;
    }

    // 「私を覚えてください」機能が必要な場合は、次のメソッドを追加することもできます
    public function getRememberToken()
    {
        return $this->attributes[$this->getRememberTokenName()] ?? null;
    }

    public function setRememberToken($value)
    {
        $this->attributes[$this->getRememberTokenName()] = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // 動的プロパティへのアクセスを提供する
    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function toArray()
    {
        return $this->attributes;
    }
}
