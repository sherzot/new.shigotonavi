<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ExportController;

class OfferController extends Controller
{

    public function registOffer(Request $request, String $id)
    {
        $staffCode = Auth::user()->staff_code;
        DB::enableQueryLog();

        // ✅ 履歴書 mavjudligini tekshirish
        $hasResume = DB::table('person_career_history')->where('staff_code', $staffCode)->exists()
            || DB::table('person_educate_history')->where('staff_code', $staffCode)->exists();
            // || DB::table('person_self_pr')->where('staff_code', $staffCode)->exists();

        // ✅ 履歴書がない場合は、履歴書ページにリダイレクト
        if (!$hasResume) {
            return redirect()->route('resume')->withErrors(['error' => 'オファーを送る前に履歴書を作成してください。']);
        }
        $job = MatchingsController::detail($id);

        if (!$job) {
            Log::error("【オファーエラー】求人情報が見つかりませんでした: OrderCode={$id}");
            return redirect()->route('mypage')->withErrors(['error' => '求人情報が見つかりませんでした。']);
        }

        Log::info("【オファー登録】処理開始: StaffCode={$staffCode}, OrderCode={$id}");
        $orderCode = $id;
        $companyCode = $job->company_code ?? null;
        $jobDetail = $job->job_type_detail;
        $companyName = $job->company_name_k;
        $branchCode = $job->branch_code ?? null;


        try {
            // ✅ ユーザー情報を取得する
            // ユーザー情報を取得する
            $userName = DB::table('master_person')
                ->where('staff_code', $staffCode)
                ->value('name');
            if (!$userName) {
                Log::error("【オファーエラー】ユーザー情報が見つかりません: StaffCode={$staffCode}");
                return redirect()->route('mypage')->withErrors(['error' => 'ユーザー情報が見つかりませんでした。']);
            }

            // ✅ 求人情報を取得する
            $job = MatchingsController::detail($id);
            if (!$job) {
                Log::error("【オファーエラー】求人情報が見つかりませんでした: OrderCode={$id}");
                return redirect()->route('mypage')->withErrors(['error' => '求人情報が見つかりませんでした。']);
            }

            if (!$companyCode || !$branchCode) {
                Log::warning("【オファー警告】求人情報が不完全: OrderCode={$id}, CompanyCode={$companyCode}, BranchCode={$branchCode}");

                $jobData = DB::table('job_order')
                    ->select('company_code', 'job_type_detail', 'branch_code')
                    ->where('order_code', $id)
                    ->first();

                if ($jobData) {
                    $companyCode = $jobData->company_code ?? $companyCode;
                    $jobDetail = $jobData->job_type_detail ?? $jobDetail;
                    $branchCode = $jobData->branch_code ?? null;
                }
            }

            // company_codeが見つからない場合はエラーを返します**
            if (!$companyCode) {
                Log::error("【オファーエラー】企業情報が見つかりません: OrderCode={$id}");
                return redirect()->route('mypage')->withErrors(['error' => '企業情報が見つかりませんでした。']);
            }
            Log::info("【デバッグ】求人情報取得: ", [
                'OrderCode' => $id,
                'CompanyCode' => $companyCode,
                'BranchCode' => $branchCode
            ]);


            // ✅ メールの送信先を決定する
            $emails = DB::table('master_offer_branch')->pluck('mail_address')->toArray();

            // ✅ `branch_code` が存在する場合は、`master_offer_branch` の `branch_code` をチェックします。
            $branchEmails = [];
            if (!empty($job->branch_code) && preg_match('/^[A-Z]{2}$/', $job->branch_code)) {
                $branchEmails = DB::table('master_offer_branch')
                    ->where('branch_code', $job->branch_code)
                    ->pluck('mail_address')
                    ->toArray();
                $emails = array_merge($emails, $branchEmails);
            }
            // ✅ この会社に所属するエージェントを探す
            $agent = DB::table('company_agent')
                ->leftJoin('master_agent', 'company_agent.agent_code', '=', 'master_agent.agent_code')
                ->where('company_agent.company_code', $companyCode)
                ->select('company_agent.agent_code', 'master_agent.mail_address')
                ->first();

            if (!$agent) {
                return redirect()->route('mypage')->withErrors(['error' => 'エージェントが見つかりません。']);
            }

            $agentCode = $agent->agent_code;

            // ✅ 特定のメールアドレスを追加
            $specialEmails = [
                'ckobayashi@lis21.co.jp',
                'tkakitani@lis21.co.jp',
                'mobata@lis21.co.jp',
                'hishiguro@lis21.co.jp',
                'kisui@lis21.co.jp'
            ];

            if (empty($job->branch_code) || preg_match('/^\d+$/', $job->branch_code)) {
                Log::info("【オファー】BranchCodeなしまたは数字: 6つのブランチマネージャに送信 + kisui@lis21.co.jp", $emails);
                $emails = array_merge($emails, ['kisui@lis21.co.jp']);
            } elseif (!empty($branchEmails)) {
                Log::info("【オファー】BranchCodeマッチ: {$job->branch_code} へ送信 + kisui@lis21.co.jp", $branchEmails);
                $emails = array_merge($emails, ['kisui@lis21.co.jp']);
            } else {
                Log::warning("【オファー警告】BranchCodeが一致しない: {$job->branch_code} → 特定のメールへ送信 + kisui@lis21.co.jp", $specialEmails);
                $emails = array_merge($emails, $specialEmails, ['kisui@lis21.co.jp']);
            }

            // ✅ 求職者は以前にこの仕事のオファーを受けたことがありますか?
            $existingOffer = DB::table('person_offer')
                ->where('staff_code', $staffCode)
                ->where('order_code', $id)
                ->select('offer_flag')
                ->first();

            if ($existingOffer) {
                if ($existingOffer->offer_flag == '1') {
                    Log::info("【オファー】既にオファー済み: StaffCode={$staffCode}, OrderCode={$id}");
                    return redirect()->route('mypage')->withErrors(['error' => 'この求人にすでにオファーしています。']);
                }

                if (in_array($existingOffer->offer_flag, ['2', '3'])) {
                    DB::table('person_offer')
                        ->where('staff_code', $staffCode)
                        ->where('order_code', $id)
                        ->update([
                            'offer_flag' => '1',
                            'update_at' => now(),
                        ]);
                    Log::info("【オファー】再オファー: StaffCode={$staffCode}, OrderCode={$id}");
                }
            } else {
                DB::table('person_offer')->insert([
                    'staff_code' => $staffCode,
                    'order_code' => $id,
                    'company_code' => $companyCode,
                    'agent_code' => $agentCode,
                    'offer_flag' => '1',
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
                Log::info("【オファー】新規オファー登録: StaffCode={$staffCode}, OrderCode={$id}");
            }

            // ✅ 履歴書 ファイルを作成する
            $exportController = new ExportController();
            $resumeFiles = $exportController->generateResumeFiles($staffCode);

            if (!$resumeFiles) {
                return redirect()->route('mypage')->withErrors(['error' => '履歴書の作成に失敗しました。']);
            }
            // ✅ ファイルの存在を確認しています
            $resumePath = storage_path("app/" . $resumeFiles['resume']);
            $careersheetPath = storage_path("app/" . $resumeFiles['careersheet']);

            if (!file_exists($resumePath) || !file_exists($careersheetPath)) {
                Log::error("【オファーエラー】履歴書 または 職務経歴書 が見つかりません。 StaffCode={$staffCode}");
                return redirect()->route('mypage')->withErrors(['error' => '履歴書 または 職務経歴書 の作成に失敗しました。']);
            }

            // ✅ メールを送信
            $maildetail = "{$staffCode} {$userName} さんが {$companyCode} {$companyName}の ({$orderCode}) {$jobDetail} にオファーしました。ご確認をお願いいします";



            foreach ($emails as $email) {
                try {
                    Mail::raw($maildetail, function ($message) use ($email, $resumePath, $careersheetPath) {
                        $message->to($email)
                            ->subject('新しいオファー通知');

                        // ✅ 可能な場合はファイルを電子メールに添付してください。
                        if (file_exists($resumePath)) {
                            $message->attach($resumePath);
                        } else {
                            Log::warning("【オファー警告】履歴書 が見つかりません: {$resumePath}");
                        }

                        if (file_exists($careersheetPath)) {
                            $message->attach($careersheetPath);
                        } else {
                            Log::warning("【オファー警告】職務経歴書 が見つかりません: {$careersheetPath}");
                        }
                    });
                    Log::info("【オファー】メール送信成功: {$email}");
                } catch (\Exception $e) {
                    Log::error("【オファーエラー】メール送信失敗: {$email}, エラー: {$e->getMessage()}");
                }
            }
            // ✅ 応募者にも完了メールを送信
            $userEmail = DB::table('master_person')->where('staff_code', $staffCode)->value('mail_address');

            try {
                Mail::send('emails.offer_complete', [
                    'userName' => $userName,
                    'jobDetail' => $jobDetail,
                    'jobUrl' => route('jobs.detail', ['id' => $orderCode]),
                    'orderCode' => $orderCode,
                ], function ($message) use ($userEmail) {
                    $message->to($userEmail)
                        ->subject('【しごとナビ】オファー完了のお知らせ');
                });

                Log::info("【オファー】応募者にもメール送信成功: {$userEmail}");
            } catch (\Exception $e) {
                Log::error("【オファーエラー】応募者へのメール送信失敗: {$userEmail}, エラー: {$e->getMessage()}");
            }

            Log::info("【オファー】処理完了: StaffCode={$staffCode}, OrderCode={$orderCode}");
            return redirect()->route('offer-completion')->with([
                'success' => "{$id} の求人案件にオファーしました。面接をお願いいたします。",
                'mode' => 'regist',
                'jobDetail' => $jobDetail
            ]);
        } catch (\Exception $e) {
            Log::error("【オファーエラー】例外発生: " . $e->getMessage());
            return redirect()->route('offer-completion')->withErrors(['error' => 'オファー登録中にエラーが発生しました。']);
        }
    }
    public function completion()
    {
        $person = Auth::user();
		if (!$person) {
			return redirect()->route('login')->withErrors('ログインしてください。');
		}

		// マッチングをチェックする
		$staffCode = $person->staff_code;
		Log::info("📌 STAFF_CODE:", ['staff_code' => $staffCode]);

        $offers = DB::table('person_offer')
            ->select('order_code', 'company_code', 'offer_flag')
            ->where('staff_code', $staffCode)
            ->get();
        //->first();

        //dd($offers);
        // $hasOffer = $offers ? true : false;
        $hasOffer = !$offers->isEmpty();

        // デフォルト値
        $jobs = [];
        $companyName = null;

        if ($hasOffer) {
            foreach ($offers as $offer) {
                //dd($offer);
                //$flags[] = $offer->offar_flag
                $job = DB::table('job_order')
                    ->join('person_offer', 'job_order.order_code', '=', 'person_offer.order_code')
                    ->select('job_order.order_code', 'job_order.company_code', 'job_order.job_type_detail', 'person_offer.offer_flag')
                    ->where('job_order.order_code', $offer->order_code)
                    ->first();

                if ($job) {
                    $jobs[] = $job;
                }
                //dd($jobs);


                $company = DB::table('master_company')
                    ->select('company_name_k')
                    ->where('company_code', $offer->company_code)
                    ->first();

                if ($company) {
                    $companyName[] = $company;
                }
            }
        }
        //🔹 `offer_flag = 3`の場合、オファーは制限される必要があります
        $hasConfirmedCancel = collect($jobs)->contains('offer_flag', '2');
        // 🔹 ユーザーの以前のオファーが期限切れになっている場合 (`offer_flag = 4`)、新しいオファーを行うことができます。
        $hasCompletedOffer = DB::table('person_offer')
            ->where('staff_code', $staffCode)
            ->where('offer_flag', '3') // オファーの期限が切れました
            ->exists();
        // return view('offer.completion');
        return view('offer-completion', compact( 'hasOffer', 'jobs', 'hasConfirmedCancel', 'companyName',  'hasCompletedOffer'));
    }
}
