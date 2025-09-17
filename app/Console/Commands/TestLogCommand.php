<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestLogCommand extends Command
{
    protected $signature = 'test:log';
    protected $description = 'テストログコマンド';

    public function handle()
    {
        Log::info('🧪 test:log コマンドが呼ばれました！');
    }
}

