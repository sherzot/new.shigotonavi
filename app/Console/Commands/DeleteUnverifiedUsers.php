<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterPerson;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeleteUnverifiedUsers extends Command
{
    // コマンド定義
    protected $signature = 'master_person:delete-unverified';

    // コマンド定義
    protected $description = '未認証のユーザーを 30 分後に削除します';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // 時間計算
        $expirationTime = Carbon::now()->subMinutes(30);

        // 未確認ユーザーを削除する
        $deletedCount = MasterPerson::whereNull('verified_at')
            ->where('created_at', '<=', $expirationTime)
            ->delete();

        // 結果をコンソールに出力する
        $this->info("$deletedCount ユーザーが削除されました。");

        // 結果の記録
        Log::info("$deletedCount 未確認のユーザーが削除されました。");
    }
}

