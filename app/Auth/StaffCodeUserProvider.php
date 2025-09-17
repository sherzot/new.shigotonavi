<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class StaffCodeUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        // 
        $user = DB::table('master_company')->where('company_code', $identifier)->first();
        if (!$user) {
            $user = DB::table('master_company')->where('company_code', $identifier)->first();
        }
        if (!$user) {
            $user = DB::table('master_agent')->where('agent_code', $identifier)->first();
        }
        return $user ? (object) $user : null;  // オブジェクトとして返す
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
            return $query->where('company_code', $credentials['company_code'])->first();
        }
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return isset($credentials['password']) && $user->set_password === md5($credentials['password']);
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): bool
    {
        return false;
    }
}
