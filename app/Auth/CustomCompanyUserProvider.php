<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class CustomCompanyUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        // `company_code` でユーザーを取得します
        $company = DB::table('master_company')->where('company_code', $identifier)->first();
        return $company ? (object) $company : null;  // オブジェクトとして返す
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // トークンが使用されていないことを忘れないでください
    }

    public function retrieveByCredentials(array $credentials)
    {
        $query = DB::table('master_company');
        if (isset($credentials['company_code'])) {
            $query->where('company_code', $credentials['company_code']);
        }
        return $query->first();  // DBから直接ユーザーデータを取得する
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $credentials['password'] === $user->password;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): bool
    {
        return false;
    }
}
