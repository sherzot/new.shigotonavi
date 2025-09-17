<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class CustomAgentUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        // `agent_code` orqali foydalanuvchini olish
        $agent = DB::table('master_agent')->where('agent_code', $identifier)->first();
        return $agent ? (object) $agent : null; // Ob'ekt sifatida qaytariladi
    }

    public function retrieveByToken($identifier, $token)
    {
        return null; // Token funksiyasi ishlatilmaydi
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Token yangilanishi kerak emas
    }

    public function retrieveByCredentials(array $credentials)
    {
        $query = DB::table('master_agent');
        if (isset($credentials['agent_code'])) {
            $query->where('agent_code', $credentials['agent_code']);
        }
        return $query->first(); // Foydalanuvchi ma'lumotlari DB dan olinadi
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Foydalanuvchi parolini tekshirish
        $hashedPassword = strtoupper(md5($credentials['password'])); // MD5 uppercase parol
        return $hashedPassword === $user->password; // Parollar mosligini qaytaradi
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): bool
    {
        // Parolni qayta hash qilishga hojat yo'q
        return false;
    }
}
