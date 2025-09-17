<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;


class MasterCompanyController extends Controller
{
    private function getAuthenticatedUser()
    {
        $companyUser = Auth::guard('master_company')->user();
        $agentUser = Auth::guard('master_agent')->user();

        if ($agentUser) {
            return ['agentUser' => $agentUser, 'companyUser' => null];
        }

        if ($companyUser) {
            return ['companyUser' => $companyUser, 'agentUser' => null];
        }

        // ユーザーが見つからない場合はログインページに戻ります
        return redirect()->route('agent.login')->withErrors(['msg' => 'このページを閲覧するにはログインが必要です。']);
    }

    public function showForm()
    {
        $authUser = $this->getAuthenticatedUser();

        if ($authUser instanceof \Illuminate\Http\RedirectResponse) {
            return $authUser;
        }

        $companyUser = $authUser['companyUser'];
        $agentUser = $authUser['agentUser'];
        // **地域情報を取得**
        $regions = DB::table('master_code')
            ->where('category_code', 'Region')
            ->get();

        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->get();

        // **地域に属する都道府県のグループ化**
        $regionGroups = $regions->map(function ($region) use ($prefectures) {
            return [
                'detail' => $region->detail,
                'prefectures' => $prefectures->filter(function ($prefecture) use ($region) {
                    return $prefecture->region_code === $region->code;
                }),
            ];
        });

        // **都道府県のリスト (個別)**
        $individualPrefectures = $prefectures->map(function ($prefecture) {
            return [
                'code' => $prefecture->code,
                'detail' => $prefecture->detail,
            ];
        })->toArray();

        // 業種
        $industryTypes = DB::table('master_code')
            ->where('category_code', 'IndustryType')
            ->get();

        return view('agent.create_company', compact(
            'agentUser',
            'regionGroups',    // 地域ごとの都道府県
            'individualPrefectures', // 各都道府県
            'industryTypes',
        ));
    }

    public function store(Request $request)
    {
        try {
            // 🔹 Step 1: バリデーションログ
            Log::info('🛂 入力検証開始');

            $validated = $request->validate([
                'company_name_k' => 'required|string|max:100',
                'company_name_f' => 'nullable|string|max:80',
                'establish_year' => 'nullable|digits:4',
                'establish_month' => 'nullable|digits_between:1,2',
                'industry_type_code' => 'nullable|string|max:3',
                'capital_amount' => 'nullable|string|max:40',
                'all_employee_num' => 'nullable|string|max:12',
                'man_employee_num' => 'nullable|string|max:12',
                'woman_employee_num' => 'nullable|string|max:12',
                'homepage_address' => 'nullable|string|max:100',
                'post_u' => 'nullable|digits:3',
                'post_l' => 'nullable|digits:4',
                'prefecture_code' => 'nullable|array',
                'prefecture_code.*' => 'nullable|exists:job_working_place,prefecture_code',
                'city_k' => 'nullable|string|max:80',
                'town' => 'nullable|string|max:80',
                'address' => 'nullable|string|max:80',
                'telephone_number' => 'nullable|string|max:40',
                'business_contents' => 'nullable|string|max:1000',
                'company_pr' => 'nullable|string|max:1000',
                'lis_person_code' => 'nullable|string|max:80',
                'lis_person_name' => 'nullable|string|max:255',
                'lis_mail_address' => 'nullable|email',
                // 'mailaddr' => 'required|email|max:50|unique:master_company,mailaddr',
                // 'mailaddr' => ['nullable', 'email', 'max:50', 'unique:master_company,mailaddr'],
                'mailaddr' => [
                    'required',
                    'email',
                    'max:50',
                    Rule::unique('master_company', 'mailaddr')->ignore($existingCompany->company_code ?? null, 'company_code'),
                ],
                'password' => 'required|string|min:3|confirmed',
                'keiyaku_ymd' => 'nullable|digits:8',
                'intbase_contract_day' => 'nullable|regex:/^\d{8}$/',
            ]);
            // 🔹 insert()dan OLDIN buni yozing:
            $prefecture = $validated['prefecture_code'] ?? [];


            Log::info('✅ バリデーション成功', $validated);

            // 🔹 Step 2: 業種名取得
            $industryType = DB::table('master_code')
                ->where('category_code', 'IndustryType')
                ->where('code', $validated['industry_type_code'])
                ->first();
            $industryTypeName = $industryType ? $industryType->detail : null;

            Log::info('🏭 業種タイプ取得', ['industryType' => $industryTypeName]);

            // 3. company_code を生成する（order_code と同じスタイルで）
            $lastCompanyCode = DB::table('master_company')
                ->orderByRaw('CAST(SUBSTRING(company_code, 2) AS UNSIGNED) DESC')
                ->value('company_code');

            $nextId = $lastCompanyCode ? intval(substr($lastCompanyCode, 1)) + 1 : 1;
            $nextCompanyCode = 'C' . str_pad($nextId, 7, '0', STR_PAD_LEFT);


            Log::info('🏢 新しい company_code 生成: ' . $nextCompanyCode);

            // 🔹 Step 4: 日付フォーマット処理
            $intbaseContractDay = !empty($validated['intbase_contract_day'])
                ? Carbon::createFromFormat('Ymd', $validated['intbase_contract_day'])->format('Y-m-d H:i:s')
                : null;

            Log::info('📆 intbase_contract_day: ' . $intbaseContractDay);


            // 🔹 Step 5: データ挿入または更新（処理判定用）
            $wasExisting = DB::table('master_company')
                ->where('mailaddr', $validated['mailaddr'])
                ->where('company_code', $nextCompanyCode)
                ->exists();


            if ($wasExisting) {
                // update
                DB::table('master_company')
                    ->where('mailaddr', $validated['mailaddr'])
                    ->update([
                        'company_code' => $nextCompanyCode,
                        'password' => strtoupper(md5($validated['password'])),
                        'company_name_k' => $validated['company_name_k'],
                        'company_name_f' => $validated['company_name_f'],
                        'establish_year' => $validated['establish_year'],
                        'establish_month' => $validated['establish_month'],
                        'industry_type' => $validated['industry_type_code'],
                        'industry_type_name' => $industryTypeName,
                        'capital_amount' => $validated['capital_amount'],
                        'all_employee_num' => $validated['all_employee_num'],
                        'man_employee_num' => $validated['man_employee_num'],
                        'woman_employee_num' => $validated['woman_employee_num'],
                        'homepage_address' => $validated['homepage_address'],
                        'post__u' => $validated['post_u'],
                        'post__l' => $validated['post_l'],
                        'prefecture' => json_encode($prefecture),
                        'city_k' => $validated['city_k'],
                        'town' => $validated['town'],
                        'address' => $validated['address'],
                        'telephone_number' => $validated['telephone_number'],
                        'business_contents' => $validated['business_contents'],
                        'company_pr' => $validated['company_pr'],
                        'lis_person_code' => $validated['lis_person_code'],
                        'lis_person_name' => $validated['lis_person_name'],
                        'lis_mail_address' => $validated['lis_mail_address'],
                        'keiyaku_ymd' => $validated['keiyaku_ymd'],
                        'intbase_contract_day' => $intbaseContractDay,
                        'updated_at' => now(),
                    ]);
            } else {
                // insert
                DB::table('master_company')->insert([
                    'company_code' => $nextCompanyCode,
                    'password' => strtoupper(md5($validated['password'])),
                    'company_name_k' => $validated['company_name_k'],
                    'company_name_f' => $validated['company_name_f'],
                    'establish_year' => $validated['establish_year'],
                    'establish_month' => $validated['establish_month'],
                    'industry_type' => $validated['industry_type_code'],
                    'industry_type_name' => $industryTypeName,
                    'capital_amount' => $validated['capital_amount'],
                    'all_employee_num' => $validated['all_employee_num'],
                    'man_employee_num' => $validated['man_employee_num'],
                    'woman_employee_num' => $validated['woman_employee_num'],
                    'homepage_address' => $validated['homepage_address'],
                    'post__u' => $validated['post_u'],
                    'post__l' => $validated['post_l'],
                    'prefecture' => json_encode($prefecture),
                    'city_k' => $validated['city_k'],
                    'town' => $validated['town'],
                    'address' => $validated['address'],
                    'telephone_number' => $validated['telephone_number'],
                    'business_contents' => $validated['business_contents'],
                    'company_pr' => $validated['company_pr'],
                    'lis_person_code' => $validated['lis_person_code'],
                    'lis_person_name' => $validated['lis_person_name'],
                    'lis_mail_address' => $validated['lis_mail_address'],
                    'mailaddr' => $validated['mailaddr'],
                    'keiyaku_ymd' => $validated['keiyaku_ymd'],
                    'intbase_contract_day' => $intbaseContractDay,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // insert into company_agent
                $user = $this->getAuthenticatedUser();
                $agentCode = $user['agentUser']->agent_code ?? null;

                if ($agentCode) {
                    DB::table('company_agent')->insert([
                        'agent_code' => $agentCode,
                        'company_code' => $nextCompanyCode,
                        'created_at' => now(),
                        'update_at' => now(),
                    ]);
                }
            }
            // 🔹 Step 6: メール送信
            $messageBody = "企業コード：{$nextCompanyCode}\n企業パスワード：{$validated['password']}\n企業名：{$validated['company_name_k']} ";
            $messageBody .= $wasExisting ? '変更されました。' : 'は登録抹消されました。';

            // 🔹 kisui@... と lis_mail_address に2通のメールが送信されます
            $recipients = ['kisui@lis21.co.jp'];

            if (!empty($validated['lis_mail_address'])) {
                $recipients[] = $validated['lis_mail_address'];
            }

            Mail::raw($messageBody, function ($message) use ($nextCompanyCode, $recipients) {
                $message->to($recipients)
                    ->subject("【企業登録完了】企業コード：{$nextCompanyCode}");
            });


            Log::info('🎉 企業登録成功: ' . $nextCompanyCode);
            return redirect()->route('agent.linked_companies')->with('success', '企業が正常に登録されました！');
        } catch (\Exception $e) {
            Log::error('❌ 会社登録エラー: ' . $e->getMessage());
            Log::error('🪵 エラー詳細: ', ['trace' => $e->getTraceAsString()]);
            return back()->withErrors([
                'msg' => '登録に失敗しました。管理者に連絡してください。',
                'error_detail' => $e->getMessage()
            ])->withInput();
        }
    }
}
