<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\MasterPerson;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Artisan コマンドを登録する
     */
    protected $commands = [
        \App\Console\Commands\SendEntryInvitationEmails::class,
        // 他のコマンドもここに追記可能
        \App\Console\Commands\ClearAllCache::class,
    ];
    //protected $middlewareGroups = [
    //    'web' => [
    //        \App\Http\Middleware\CustomSessionGuard::class,
    //        \Illuminate\Session\Middleware\StartSession::class,
    //        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    //    ],
    //]

    protected function schedule(Schedule $schedule)
    {
        Log::info('✅ schedule() が呼び出されました');
        $schedule->command('test:log')->everyMinute(); //20250407
        
        // $schedule->command('master_person:delete-unverified')->everyThirtyMinutes();

        // Log::info('DeleteUnverifiedUsers command scheduled successfully.');

        // 新しく追加するスケジュール（毎週月曜朝8時に実行）
        //$schedule->command('email:send-entry-invitations')->weeklyOn(0, '15:00');//; //(1, '08:00');
        $schedule->command('email:send-entry-invitations')
            //$schedule->command('email:SendEntryInvitationEmails')
            ->everyMinute()
            ->before(function () {
                Log::info('スケジュール開始前');
            })
            ->after(function () {
                Log::info('スケジュール実行完了');
            });

        Log::info('Send Email-to-user command scheduled successfully.');
    }

    // protected function schedule(Schedule $schedule)
    // {
    //     $schedule->call(function () {
    //         MasterPerson::whereNull('verified_at')
    //         ->where('created_at', '<', now()->subMinutes())
    //         ->delete();


    //         Log::info('30 分以上経過した未確認のユーザーは削除されました。');
    //     })->everyThirtyMinutes();
    // }


    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
