<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class MailConfigController extends Controller
{
    public function index()
    {
        // Mail config ko'rish
        $mailConfig = [
            'MAIL_MAILER' => config('mail.default'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => '********',
            'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'MAIL_FROM_NAME' => config('mail.from.name'),
        ];
        return view('mail-config', compact('mailConfig'));
    }

    public function refreshConfig()
    {
        Artisan::call('config:clear');
        Artisan::call('config:cache');
        return redirect()->back()->with('status', 'Configuration reloaded!');
    }
}
