<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
//use App\Models\MasterPerson;i
use Illuminate\Support\Facades\DB;
use App\Mail\EntryInvitationMail;
use Illuminate\Support\Facades\Log;
//use Carbon\Carbon;

class SendEntryInvitationEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //protected $signature = 'app:send-entry-invitation-emails';
    protected $signature = 'email:send-entry-invitations';
    protected $description = '登録から1〜2週間経過したユーザーにエントリーのメールを送る';


    /**
     * The console command description.
     *
     * @var string
     */
    //protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
	DB::enableQueryLog();
	Log::info('メール送信バッチ：開始');
        $from = Carbon::now()->subWeeks(2)->startOfDay();
        $to = Carbon::now()->subWeek()->endOfDay();
	$staffCode = 'S1412495';

        //$persons = MasterPerson::whereBetween('created_at', [$from, $to])->get();
	$persons = DB::table('master_person')
		//->whereBetween('created_at', [$from, $to]) //本番
		->where('staff_code', $staffCode)
		->select('staff_code', 'mail_address')
		->get();
	Log::info(DB::getQueryLog());

        foreach ($persons as $person) {
	      Log::info('送信先メールアドレス: ' . $person->mail_address);
	    $message = "ようこそしごとナビへ！";//"※ここは冒頭に挿入したい自由文など";
            // キューに送信を任せる（メールは非同期処理）
            Mail::to($person->mail_address)
                ->queue(new EntryInvitationMail($message));
        }// end foreach
	  Log::info('メール送信バッチ：完了');

        $this->info('対象ユーザーにメールを送信しました。');
    //}

        
    }//end function
} //end class
