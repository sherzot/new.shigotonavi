<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CompanyDashboardController extends Controller
{
    public function dashboard()
    {
        // 許可されたユーザー情報を取得します
        $companyUser = Auth::guard('master_company')->user();

        // 許可されたユーザーデータのロギング
        Log::info('管理情報:', ['companyUser' => $companyUser]);

        // ダッシュボードビューを表示
        return view('jobs.company_dashboard', ['companyUser' => $companyUser]);
    }
    private function getAuthenticatedUser()
    {
        $companyUser = Auth::guard('master_company')->user();
        $agentUser = Auth::guard('master_agent')->user();

        if ($companyUser) {
            return ['companyUser' => $companyUser, 'agentUser' => null];
        }

        if ($agentUser) {
            return ['agentUser' => $agentUser, 'companyUser' => null];
        }

        return redirect()->route('company.login')->withErrors(['msg' => 'このページを閲覧するにはログインが必要です。']);
    }

    /**
     * 企業の求人リストを表示
     */
    public function showJobsList(Request $request)
    {
        $authUser = $this->getAuthenticatedUser();
        // dd($authUser); // ➜`companyUser` が null でないかどうかを確認するには、これをチェックしてください。

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $companyUser = $authUser['companyUser'];
        $agentUser = $authUser['agentUser'];

        // ❗️ companyUserが`null`の場合、エラーが発生します。
        if (!$companyUser) {
            abort(403, '企業kのユーザーが認証されていません');
        }
        // 🔍 企業の求人情報を取得する
        if ($companyUser) {
            $jobs = DB::table('job_order')
                ->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
                ->join('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
                ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
                ->leftJoin('log_access_history_order', 'job_order.order_code', '=', 'log_access_history_order.order_code')
                ->leftJoin('master_code', function ($join) {
                    $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                        ->where('master_code.category_code', '=', 'Prefecture');
                })
                ->where('job_order.company_code', $companyUser->company_code)
                ->select(
                    'job_order.order_code',
                    'job_order.order_type',
                    'job_order.job_type_detail',
                    'job_order.public_flag',
                    DB::raw('DATE(job_order.public_day) as public_day'),
                    DB::raw('DATE(job_order.public_limit_day) as public_limit_day'),
                    'job_order.created_at',
                    'job_order.update_at',
                    'master_company.company_name_k as company_name',
                    'job_working_place.city',
                    'job_working_place.town',
                    DB::raw('GROUP_CONCAT(DISTINCT master_code.detail SEPARATOR ", ") as all_prefectures'),
                    DB::raw('COALESCE(SUM(log_access_history_order.browse_cnt), 0) as browse_cnt')
                )

                ->groupBy(
                    'job_order.order_code',
                    'job_order.order_type',
                    'job_order.job_type_detail',
                    'job_order.public_flag',
                    'job_order.public_day',
                    'job_order.public_limit_day',
                    'job_order.created_at',
                    'job_order.update_at',
                    'master_company.company_name_k',
                    'job_working_place.city',
                    'job_working_place.town'
                )
                ->orderBy('job_order.update_at', 'desc')
                ->paginate(6)
                ->appends($request->all());
        } else {
            // 空の`Paginator`オブジェクトを手動で作成する
            $jobs = new LengthAwarePaginator(
                collect(), // 空のコレクション
                0,         // 要素の総数
                6,         // ページあたりの要素数
                $request->input('page', 1), // 現在のページ (デフォルト: 1)
                ['path' => $request->url(), 'query' => $request->query()] // URLとクエリ文字列
            );
        }
        // dd($jobs->toArray());


        return view('jobs.job_list', compact('agentUser', 'companyUser', 'jobs'));
    }




    /**
     * 求人の詳細を表示
     */
    public function detail($id)
    {
        // 会社のユーザーを取得
        $companyUser = Auth::guard('master_company')->user();

        // ユーザー認証
        if (!$companyUser) {
            return redirect()->route('company.login')->withErrors(['msg' => 'このページを閲覧するにはログインが必要です。']);
        }

        // 会社のcompany_codeを取得する
        $companyCode = $companyUser->company_code;

        // 会社 `order_code` の求人情報を取得する (すべての列)
        $job = DB::table('job_order')
            ->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
            ->join('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
            ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
            ->join('master_code', function ($join) {
                $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                    ->where('master_code.category_code', '=', 'Prefecture');
            })
            ->leftJoin('job_skill', 'job_order.order_code', '=', 'job_skill.order_code')
            ->leftJoin('master_code as skill_master', function ($join) {
                $join->on('job_skill.category_code', '=', 'skill_master.category_code')
                    ->on('job_skill.code', '=', 'skill_master.code');
            })
            ->join('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
            ->select(
                'job_order.*', // 🔹 すべての列を取得
                'master_company.company_name_k as company_name',
                DB::raw('GROUP_CONCAT(DISTINCT master_code.detail SEPARATOR ", ") as all_prefectures'),
                DB::raw('GROUP_CONCAT(DISTINCT skill_master.detail SEPARATOR ", ") as skill_detail'),
                'job_working_place.city',
                'job_working_place.town',
                'job_working_place.address',
                'job_job_type.job_type_code',
                'job_order.public_flag',
                'job_supplement_info.*'
            )
            ->where('job_order.company_code', $companyCode) // 🔹 この会社の求人票のみ
            ->where('job_order.order_code', $id) // 🔹 この`order_code`の情報
            ->groupBy(
                'job_order.order_code',
                'master_company.company_name_k',
                'job_working_place.city',
                'job_working_place.town',
                'job_working_place.address',
                'job_job_type.job_type_code',
                'job_supplement_info.pr_title1',
                'job_order.public_flag',
            )
            ->first();

        // 求人票が見つからない場合
        if (!$job) {
            return redirect()->route('jobs.job_list')->withErrors(['msg' => 'ジョブの詳細は見つかりませんでした。']);
        }

        // ✅ 給与の種類のより正確な定義
        if (!is_null($job->yearly_income_min) && $job->yearly_income_min > 0) {
            $desiredSalaryType = '年収';
            $salary_min = $job->yearly_income_min;
            $salary_max = $job->yearly_income_max;
        } elseif (!is_null($job->monthly_income_min) && $job->monthly_income_min > 0) {
            $desiredSalaryType = '月給';
            $salary_min = $job->monthly_income_min;
            $salary_max = $job->monthly_income_max;
        } elseif (!is_null($job->daily_income_min) && $job->daily_income_min > 0) {
            $desiredSalaryType = '日給';
            $salary_min = $job->daily_income_min;
            $salary_max = $job->daily_income_max;
        } elseif (!is_null($job->hourly_income_min) && $job->hourly_income_min > 0) {
            $desiredSalaryType = '時給';
            $salary_min = $job->hourly_income_min;
            $salary_max = $job->hourly_income_max;
        } else {
            $desiredSalaryType = null;
            $salary_min = null;
            $salary_max = null;
        }

        return view('jobs.job_detail', compact('job', 'desiredSalaryType', 'salary_min', 'salary_max', 'companyUser'));
    }

    /**
     * 🔹 募集の一時停止 (Ishni vaqtincha to'xtatish)
     */
    public function pauseJob(Request $request)
    {
        $companyUser = Auth::guard('master_company')->user();
        if (!$companyUser) {
            return response()->json(['status' => 'error', 'message' => 'ログインが必要です。'], 403);
        }

        $orderCode = $request->input('order_code');

        $affectedRows = DB::table('job_order')
            ->where('order_code', $orderCode)
            ->where('company_code', $companyUser->company_code) // ❌ 他の企業に影響を与えないようにするため
            ->update([
                'public_flag' => 0, // 🔴 仕事をやめる
                'order_progress_type' => 2,
                'update_at' => now(),
            ]);

        if ($affectedRows > 0) {
            return response()->json(['status' => 'success', 'message' => '募集が一時停止されました。']);
        } else {
            return response()->json(['status' => 'error', 'message' => '募集の一時停止に失敗しました。'], 400);
        }
    }

    /**
     * 🔹 募集開始 (Ishni boshlash)
     */
    public function startJob(Request $request)
    {
        $companyUser = Auth::guard('master_company')->user();
        if (!$companyUser) {
            return response()->json(['status' => 'error', 'message' => 'ログインが必要です。'], 403);
        }

        $orderCode = $request->input('order_code');

        $affectedRows = DB::table('job_order')
            ->where('order_code', $orderCode)
            ->where('company_code', $companyUser->company_code)
            ->update([
                'public_flag' => 1, // 🟢 仕事の活性化
                'order_progress_type' => 1,
                'update_at' => now(),
            ]);

        if ($affectedRows > 0) {
            return response()->json(['status' => 'success', 'message' => '募集が開始されました。']);
        } else {
            return response()->json(['status' => 'error', 'message' => '募集開始に失敗しました。'], 400);
        }
    }
}
