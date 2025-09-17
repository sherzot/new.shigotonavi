<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\AgentNotification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;


class CreateJobController extends Controller
{
    /**
     * 求人の作成フォームを表示する
     */
    public function showCreateJobPage(Request $request)
    {
        // 現在ログインしている企業のユーザーを取得
        $companyUser = Auth::guard('master_company')->user();

        if (!$companyUser) {
            return redirect()->route('company.login')->withErrors(['msg' => 'ログインが必要です']);
        }

        // **大クラス (業種) を取得**
        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();

        // **ライセンスのグループを取得**
        $groups = DB::table('master_license')
            ->select('group_code', 'group_name')
            ->distinct()
            ->get();

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

        // **チェックボックスオプション**
        $checkboxOptions = $this->checkboxOptions();
        // **SchoolTypeデータの取得**
        $academicOptions = DB::table('master_code')
            ->where('category_code', 'SchoolType')
            ->select('code', 'detail')
            ->get();
        // **勤務形態データ (OrderType) を取得**
        $orderTypes = DB::table('master_code')
            ->where('category_code', 'OrderType')
            ->whereIn('code', [1, 2, 3]) // 値1、2、3のみを取得する
            ->select('code', 'detail')
            ->get();
        $categoryOptions = [];
        $licenseOptions = [];

        $oldQualifications = old('qualifications', []);

        foreach ($oldQualifications as $i => $qualification) {
            // カテゴリを取得
            if (!empty($qualification['group_code'])) {
                $categoryOptions[$i] = DB::table('master_license')
                    ->select('category_code', 'category_name')
                    ->where('group_code', $qualification['group_code'])
                    ->distinct()
                    ->get()
                    ->toArray();
            }

            // 資格を取得（修正版）←🔧 ここを修正
            if (!empty($qualification['group_code']) && !empty($qualification['category_code'])) {
                $licenseOptions[$i] = DB::table('master_license')
                    ->select('code', 'name')
                    ->where('group_code', $qualification['group_code'])
                    ->where('category_code', $qualification['category_code'])
                    ->get()
                    ->map(function ($row) {
                        return (array) $row;
                    })
                    ->toArray();
            }
        }

        return view('jobs.create_job', compact(
            'orderTypes',      // 勤務形態
            'bigClasses',      // 業種
            'groups',          // ライセンスグループ
            'categoryOptions',
            'licenseOptions',
            'regionGroups',    // 地域ごとの都道府県
            'individualPrefectures', // 各都道府県
            'academicOptions', // 学歴の選択肢
            'checkboxOptions',
            'companyUser'
        ));
    }

    /**
     * 大クラスに基づいて職種リストを取得
     */
    public function getJobTypes(Request $request)
    {
        $bigClassCode = $request->input('big_class_code');

        if (!$bigClassCode) {
            return response()->json(['error' => '業種が選択されていません'], 400);
        }

        $jobTypes = DB::table('master_job_type')
            ->where('big_class_code', $bigClassCode)
            ->select('middle_class_code', 'middle_clas_name')
            ->get();

        return response()->json($jobTypes);
    }

    /**
     * ライセンスカテゴリの取得
     */
    public function getLicenseCategories(Request $request)
    {
        $groupCode = $request->input('group_code');

        if (!$groupCode) {
            return response()->json(['error' => 'Invalid Group Code'], 400);
        }

        $categories = DB::table('master_license')
            ->where('group_code', $groupCode)
            ->select('category_code', 'category_name')
            ->distinct()
            ->get();

        return response()->json($categories);
    }


    public function getLicenses(Request $request)
    {
        $groupCode = $request->input('group_code');
        $categoryCode = $request->input('category_code');

        if (!$groupCode || !$categoryCode) {
            return response()->json(['error' => 'Invalid Group or Category Code'], 400);
        }

        $licenses = DB::table('master_license')
            ->where('group_code', $groupCode)
            ->where('category_code', $categoryCode)
            ->select('code', 'name')
            ->distinct()
            ->get();

        return response()->json($licenses);
    }

    /**
     * 特記事項チェックボックスリスト
     */
    private function checkboxOptions()
    {
        return [
            'inexperienced_person_flag' => '未経験者OK',
            'balancing_work_flag' => '仕事と生活のバランス',
            'ui_turn_flag' => 'UIターン',
            'many_holiday_flag' => '休日120日',
            'flex_time_flag' => 'フレックス',
            'near_station_flag' => '駅近5分',
            'no_smoking_flag' => '禁煙分煙',
            'newly_built_flag' => '新築',
            'landmark_flag' => '高層ビル',
            'renovation_flag' => '改装改築',
            'designers_flag' => 'デザイン',
            'company_cafeteria_flag' => '社員食堂',
            'short_overtime_flag' => '残業少なめ',
            'maternity_flag' => '産休育休',
            'dress_free_flag' => '服装自由',
            'mammy_flag' => '主婦(夫)歓迎',
            'fixed_time_flag' => '固定時間勤務',
            'short_time_flag' => '短時間勤務',
            'handicapped_flag' => '障がい者歓迎',
            'rent_all_flag' => '住宅全額補助',
            'rent_part_flag' => '住宅一部補助',
            'meals_flag' => '食事付き',
            'meals_assistance_flag' => '食事補助',
            'training_cost_flag' => '研修費用支給',
            'entrepreneur_cost_flag' => '起業補助',
            'money_flag' => '金銭補助',
            'telework_flag' => 'テレワーク可',
            'land_shop_flag' => '店舗提供',
            'find_job_festive_flag' => '就職祝金',
            'appointment_flag' => '正社員登用',
        ];
    }

    // データをDBに保存する
    public function storeJob(Request $request, NotificationService $notificationService)
    {
        // 1. 現在ログインしている企業のユーザーを取得
        $companyUser = Auth::guard('master_company')->user();
        $checkboxOptions = $this->checkboxOptions();
        Log::info('✅ 企業ユーザー取得成功:', ['company_code' => $companyUser->company_code]);
        Log::info('✅ チェックボックスオプション取得:', $checkboxOptions);

        // 2. 入力データの検証
        $validatedData = $request->validate([
            'order_type' => 'nullable|exists:master_code,code',
            'order_progress_type' => 'nullable|in:1,2',
            'public_flag' => 'nullable|boolean',
            'job_type_detail' => 'nullable|string|max:1000',
            'big_class_code' => 'nullable|array|min:1',
            'big_class_code.*' => 'nullable|exists:master_job_type,big_class_code',
            'job_category' => 'nullable|array|min:1',
            'job_category.*' => 'nullable|exists:master_job_type,middle_class_code',
            'pr_title1' => 'nullable|string',
            'pr_contents1' => 'required|string|max:1000',
            'pr_title2' => 'nullable|string',
            'pr_contents2' => 'nullable|string|max:1000',
            'pr_title3' => 'nullable|string',
            'pr_contents3' => 'nullable|string|max:1000',
            'business_detail' => 'nullable|string',
            'BestMatch' => 'nullable|string|max:1000',
            'public_limit_day' => 'nullable|regex:/^\d{8}$/',
            'company_speciality' => 'nullable|string|max:1000',
            'catch_copy' => 'nullable|string|max:1000',
            'biz_name1' => 'nullable|string|max:1000',
            'biz_percentage1' => 'nullable|numeric|min:0|max:100',
            'biz_name2' => 'nullable|string|max:1000',
            'biz_percentage2' => 'nullable|numeric|min:0|max:100',
            'biz_name3' => 'nullable|string|max:1000',
            'biz_percentage3' => 'nullable|numeric|min:0|max:100',
            'biz_name4' => 'nullable|string|max:1000',
            'biz_percentage4' => 'nullable|numeric|min:0|max:100',
            'yearly_income_min' => 'nullable|numeric|min:0',
            'yearly_income_max' => 'nullable|numeric|gte:yearly_income_min',
            'monthly_income_min' => 'nullable|numeric|min:0',
            'monthly_income_max' => 'nullable|numeric|gte:monthly_income_min',
            'hourly_income_min' => 'nullable|numeric|min:0',
            'hourly_income_max' => 'nullable|numeric|gte:hourly_income_min',
            'daily_income_min' => 'nullable|numeric|min:0',
            'daily_income_max' => 'nullable|numeric|gte:daily_income_min',
            'income_remark' => 'nullable|string|max:1000',
            'employment_start_day' => ['nullable', 'regex:/^\d{8}$/'],
            'work_start_day' => ['nullable', 'regex:/^\d{8}$/'],
            'work_end_day' => ['nullable', 'regex:/^\d{8}$/'],
            'work_update_flag' => 'nullable|in:0,1',
            'work_period' => ['nullable', 'numeric', 'min:1'],
            'work_start_time' => 'nullable|regex:/^\d{4}$/',
            'Work_end_time' => 'nullable|regex:/^\d{4}$/',
            'rest_start_time' => 'nullable|regex:/^\d{4}$/',
            'rest_end_time' => 'nullable|regex:/^\d{4}$/',
            'over_work_flag' => 'nullable|boolean',
            'work_time_remark' => 'nullable|string|max:1000',
            'weekly_holiday_type' => 'nullable|string|in:001,002,003,004,999',
            'holiday_remark' => 'nullable|string|max:1000',
            'prefecture_code' => 'nullable|array|min:1',
            'prefecture_code.*' => 'exists:job_working_place,prefecture_code',
            'city' => 'nullable|string|max:1000',
            'town' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:1000',
            'section' => 'nullable|string|max:1000',
            'telephone_number' => 'nullable|string|max:15',
            'charge_person_post' => 'nullable|string|max:1000',
            'charge_person_name' => 'nullable|string|max:1000',
            'age_min' => 'nullable|integer|min:0',
            'age_max' => 'nullable|integer|gte:age_min',
            'age_reason_flag' => 'nullable|in:K,L,M,N,O,P',
            'qualifications' => 'nullable|array',
            'qualifications.*.group_code' => 'nullable|exists:master_license,group_code',
            'qualifications.*.category_code' => 'nullable|exists:master_license,category_code',
            'qualifications.*.code' => 'nullable|exists:master_license,code',
            'hope_school_history_code' => 'nullable|exists:master_code,code',
            'graduation_year' => 'nullable|integer|digits:4|min:1900',
            'new_graduate_flag' => 'nullable|boolean',
            'employee_restaurant_flag' => 'nullable|boolean',
            'board_flag' => 'nullable|boolean',
            'smoking_flag' => 'nullable|boolean',
            'smoking_area_flag' => 'nullable|boolean',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|array',
            'skills.*.*' => 'nullable|exists:master_code,code',
            'supplement_flags' => 'nullable|array',
            'supplement_flags.*' => 'nullable|in:' . implode(',', array_keys($checkboxOptions)),
            'process1' => 'nullable|string|max:1000',
            'process2' => 'nullable|string|max:1000',
            'process3' => 'nullable|string|max:1000',
            'process4' => 'nullable|string|max:1000',
            'employee_code' => 'nullable|string|max:255',
        ]);
        Log::info('Checkbox options:', $checkboxOptions);
        // dd($validatedData);
        // dd($validatedData);
        // 4. job_orderデータを保存する
        try {
            $orderCode = null; // トップスコープで宣言
            DB::transaction(function () use ($validatedData, $request, $companyUser, $notificationService) {
                // ✅ 1. order_code を生成する
                $lastOrderCode = DB::table('job_order')
                    ->orderByRaw('CAST(SUBSTRING(order_code, 2) AS UNSIGNED) DESC')
                    ->value('order_code');

                $nextId = $lastOrderCode ? intval(substr($lastOrderCode, 1)) + 1 : 1;
                $orderCode = 'J' . str_pad($nextId, 7, '0', STR_PAD_LEFT);
                Log::info('✅ 新しいorder_code生成:', ['order_code' => $orderCode]);
                // ✅ 2. Insert into job_order
                DB::table('job_order')->insert([
                    'order_code' => $orderCode,
                    'company_code' => $companyUser->company_code,
                    'public_limit_day' => $validatedData['public_limit_day']
                        ? Carbon::createFromFormat('Ymd', $validatedData['public_limit_day'])->format('Y-m-d H:i:s')
                        : '0000-00-00 00:00:00',
                    'order_type' => $validatedData['order_type'],
                    'order_progress_type' => $validatedData['order_progress_type'],
                    'public_flag' => $validatedData['public_flag'],
                    'business_detail' => $validatedData['business_detail'],
                    'job_type_detail' => $validatedData['job_type_detail'],
                    'income_remark' => $validatedData['income_remark'],
                    'over_work_flag' => $validatedData['over_work_flag'],
                    'work_time_remark' => $validatedData['work_time_remark'],
                    'holiday_remark' => $validatedData['holiday_remark'] ?? '',
                    'weekly_holiday_type' => $validatedData['weekly_holiday_type'] ?? '000',
                    'hope_school_history_code' => $validatedData['hope_school_history_code'],
                    'employee_restaurant_flag' => $validatedData['employee_restaurant_flag'] ?? 0,
                    'smoking_flag' => $validatedData['smoking_flag'] ?? 0,
                    'smoking_area_flag' => $validatedData['smoking_area_flag'] ?? 0,
                    'board_flag' => $validatedData['board_flag'] ?? 0,
                    'public_day' => now(),
                    'created_at' => now(),
                    'update_at' => now(),
                    'yearly_income_min' => $validatedData['yearly_income_min'] ?? 0,
                    'yearly_income_max' => $validatedData['yearly_income_max'] ?? 0,
                    'monthly_income_min' => $validatedData['monthly_income_min'] ?? 0,
                    'monthly_income_max' => $validatedData['monthly_income_max'] ?? 0,
                    'hourly_income_min' => $validatedData['hourly_income_min'] ?? 0,
                    'hourly_income_max' => $validatedData['hourly_income_max'] ?? 0,
                    'daily_income_min' => $validatedData['daily_income_min'] ?? 0,
                    'daily_income_max' => $validatedData['daily_income_max'] ?? 0,
                    'age_min' => $validatedData['age_min'] ?? null,
                    'age_max' => $validatedData['age_max'] ?? null,
                    'age_reason_flag' => $validatedData['age_reason_flag'] ?? null,
                    'employee_code' => $validatedData['employee_code'] ?? '',
                ]);
                // job_order登録
                Log::info('✅ job_order保存成功');

                // job_job_typeデータを保存する
                // ✅ 3. 手動IDでjob_job_typeに挿入
                $lastTypeId = 0;
                foreach ($validatedData['big_class_code'] as $i => $big) {
                    $middle = $validatedData['job_category'][$i] ?? null;
                    if ($middle) {
                        $typeCode = $big . $middle . '000';
                        DB::table('job_job_type')->insert([
                            'order_code' => $orderCode,
                            'id' => ++$lastTypeId,
                            'job_type_code' => $typeCode,
                            'created_at' => now(),
                            'update_at' => now(),
                        ]);
                    }
                }
                // job_job_type登録
                Log::info('✅ job_job_type保存成功');

                // ✅ 4. 手動IDでjob_licenseに挿入
                $lastLicenseId = 0;
                foreach ($validatedData['qualifications'] ?? [] as $q) {
                    // Faqat barcha 3 ta qiymat mavjud bo‘lsa, insert qilamiz
                    if (!empty($q['group_code']) && !empty($q['category_code']) && !empty($q['code'])) {
                        DB::table('job_license')->insert([
                            'order_code' => $orderCode,
                            'id' => ++$lastLicenseId,
                            'group_code' => $q['group_code'],
                            'category_code' => $q['category_code'],
                            'code' => $q['code'],
                            'created_at' => now(),
                            'update_at' => now(),
                        ]);
                    }
                }
                // job_license登録
                Log::info('✅ job_license保存成功');

                // job_skillデータを保存する
                // ✅ 5. Insert into job_skill with manual ID
                $lastSkillId = 0;
                foreach ($validatedData['skills'] ?? [] as $cat => $skills) {
                    foreach ($skills as $code) {
                        DB::table('job_skill')->insert([
                            'order_code' => $orderCode,
                            'id' => ++$lastSkillId,
                            'category_code' => $cat,
                            'code' => $code,
                            'period' => 0,
                            'created_at' => now(),
                            'update_at' => now(),
                        ]);
                    }
                }
                // job_skill登録
                Log::info('✅ job_skill保存成功');

                // job_supplement_infoデータを保存する
                // ✅ 6. Insert into job_supplement_info
                $supplement = [
                    'order_code' => $orderCode,
                    'company_code' => $companyUser->company_code,
                    'process1' => $validatedData['process1'] ?? '',
                    'process2' => $validatedData['process2'] ?? '',
                    'process3' => $validatedData['process3'] ?? '',
                    'process4' => $validatedData['process4'] ?? '',
                    'company_speciality' => $validatedData['company_speciality'] ?? '',
                    'catch_copy' => $validatedData['catch_copy'] ?? '',
                    'pr_title1' => $validatedData['pr_title1'] ?? '',
                    'pr_title2' => $validatedData['pr_title2'] ?? '',
                    'pr_title3' => $validatedData['pr_title3'] ?? '',
                    'pr_contents1' => $validatedData['pr_contents1'] ?? '',
                    'pr_contents2' => $validatedData['pr_contents2'] ?? '',
                    'pr_contents3' => $validatedData['pr_contents3'] ?? '',
                    'biz_name1' => $validatedData['biz_name1'] ?? '',
                    'biz_name2' => $validatedData['biz_name2'] ?? '',
                    'biz_name3' => $validatedData['biz_name3'] ?? '',
                    'biz_name4' => $validatedData['biz_name4'] ?? '',
                    'biz_percentage1' => $validatedData['biz_percentage1'] ?? 0,
                    'biz_percentage2' => $validatedData['biz_percentage2'] ?? 0,
                    'biz_percentage3' => $validatedData['biz_percentage3'] ?? 0,
                    'biz_percentage4' => $validatedData['biz_percentage4'] ?? 0,
                    'created_at' => now(),
                    'update_at' => now(),
                ];
                foreach ($this->checkboxOptions() as $key => $label) {
                    $supplement[$key] = in_array($key, $validatedData['supplement_flags'] ?? []) ? 1 : 0;
                }
                DB::table('job_supplement_info')->insert($supplement);
                // job_supplement_info登録
                Log::info('✅ job_supplement_info保存成功');

                // job_noteデータを保存する
                // ✅ 7. Insert into job_note
                DB::table('job_note')->insert([
                    'order_code' => $orderCode,
                    'category_code' => 'Note',
                    'code' => 'BestMatch',
                    'note' => $request->input('BestMatch') ?? '',
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
                // job_note登録
                Log::info('✅ job_note保存成功');

                // job_scheduled_to_intraduntionデータを保存する
                // ✅ 8. Insert into job_scheduled_to_intraduntion
                DB::table('job_scheduled_to_intraduntion')->insert([
                    'order_code' => $orderCode,
                    'employment_start_day' => $validatedData['employment_start_day']
                        ? Carbon::createFromFormat('Ymd', $validatedData['employment_start_day'])->format('Y-m-d H:i:s')
                        : null,
                    'work_start_day' => $validatedData['work_start_day']
                        ? Carbon::createFromFormat('Ymd', $validatedData['work_start_day'])->format('Y-m-d H:i:s')
                        : null,
                    'work_end_day' => $validatedData['work_end_day']
                        ? Carbon::createFromFormat('Ymd', $validatedData['work_end_day'])->format('Y-m-d H:i:s')
                        : null,
                    'work_period' => $validatedData['work_period'] ?? 0,
                    'work_update_flag' => $validatedData['work_update_flag'] ?? 0,
                    'new_graduate_flag' => $validatedData['new_graduate_flag'] ?? 0,
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
                // job_scheduled_to_intraduntion登録
                Log::info('✅ job_scheduled_to_intraduntion保存成功');

                // job_working_conditionデータを保存する
                // ✅ 9. Insert into job_working_condition
                $lastCondId = DB::table('job_working_condition')
                    ->where('order_code', $orderCode)
                    ->max('id') ?? 0;
                DB::table('job_working_condition')->insert([
                    'order_code' => $orderCode,
                    'id' => ++$lastCondId,
                    'work_start_time' => $validatedData['work_start_time'],
                    'Work_end_time' => $validatedData['Work_end_time'],
                    'rest_start_time' => $validatedData['rest_start_time'],
                    'rest_end_time' => $validatedData['rest_end_time'],
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
                // job_working_condition登録
                Log::info('✅ job_working_condition保存成功');

                // job_working_placeデータを保存する
                // ✅ 10. Insert into job_working_place
                foreach ($validatedData['prefecture_code'] ?? [] as $idx => $pref) {
                    DB::table('job_working_place')->insert([
                        'order_code' => $orderCode,
                        'working_place_seq' => $idx + 1,
                        'area' => '日本',
                        'prefecture_code' => $pref,
                        'city' => $validatedData['city'] ?? '',
                        'town' => $validatedData['town'] ?? '',
                        'address' => $validatedData['address'] ?? '',
                        'section' => $validatedData['section'] ?? '',
                        'telephone_number' => $validatedData['telephone_number'] ?? '',
                        'charge_person_post' => $validatedData['charge_person_post'] ?? '',
                        'charge_person_name' => $validatedData['charge_person_name'] ?? '',
                    ]);
                }
                // job_working_place登録
                Log::info('✅ job_working_place保存成功');
                // ✅ 11. メールを送信
                $this->sendAgentNotification($orderCode, $validatedData['order_type'] ?? null, 'create');
            });
            Log::info('✅ 全ての求人票保存処理が完了しました。');
            return redirect()
                ->route('jobs.job_list')
                ->with('success', "新しい求人票 {$orderCode} が作成されました");
        } catch (\Throwable $e) {
            Log::error('❌ 求人票作成エラー', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => '保存中にエラーが発生しました。'])->withInput();
        }
    }
    /**
     * エージェントにメッセージを送信
     */
    private function sendAgentNotification($orderCode, $orderType, $action)
    {
        $agent = DB::table('master_agent')
            ->join('company_agent', 'master_agent.agent_code', '=', 'company_agent.agent_code')
            ->join('job_order', 'company_agent.company_code', '=', 'job_order.company_code')
            ->where('job_order.order_code', $orderCode)
            ->select('master_agent.mail_address', 'master_agent.agent_code')
            ->first();

        if (!$agent) {
            Log::error("エージェントが見つかりません: OrderCode - $orderCode");
            return;
        }

        $subject = match ($action) {
            'create' => "新しい求人票が作成されました: OrderCode - $orderCode",
            'update' => "{$orderCode} 求人票変更されました",
            default => "求人票の更新通知: OrderCode - $orderCode",
        };

        $message = match ($orderType) {
            1 => match ($action) {
                'create' => "時給制の求人票 ({$orderCode}) が作成されました。契約に基づいて時給を入力します。",
                'update' => "時給制の求人票 ({$orderCode}) が変更されました。詳細を確認してください。",
                default => "時給制の求人票 ({$orderCode}) に変更が加えられました。",
            },
            3 => match ($action) {
                'create' => "時給制の求人票 ({$orderCode}) が作成されました。契約に基づいて時給を入力します。",
                'update' => "時給制の求人票 ({$orderCode}) が変更されました。詳細を確認してください。",
                default => "時給制の求人票 ({$orderCode}) に変更が加えられました。",
            },
            default => match ($action) {
                'create' => "新しい求人票 ({$orderCode}) が作成されました。詳細を確認してください。",
                'update' => "求人票 ({$orderCode}) が変更されました。詳細を確認してください。",
                default => "求人票 ({$orderCode}) に変更が加えられました。",
            },
        };
        if (empty($agent->mail_address)) {
            Log::error("エージェントのメールアドレスが見つかりません: AgentCode - {$agent->agent_code}");
            return;
        }

        try {
            Mail::to($agent->mail_address)->send(new \App\Mail\AgentNotification($subject, $message));
            Log::info("メッセージはエージェントに正常に送信されました: {$agent->mail_address}");
        } catch (\Exception $e) {
            Log::error("エージェントへのメール送信エラー: {$e->getMessage()}");
        }
    }

    /**
     * 編集作業のページを表示
     */
    public function showEditJobPage($orderCode)
    {
        // 現在ログインしているユーザーを確認する
        $companyUser = Auth::guard('master_company')->user();

        if (!$companyUser) {
            return redirect()->route('company.login')->withErrors(['msg' => 'ログインする必要があります。.']);
        }

        // 基本的な仕事情報を入手する
        $job = DB::table('job_order')->where('order_code', $orderCode)->first();

        if (!$job) {
            return redirect()->route('jobs.job_list')->withErrors(['msg' => '求人が見つかりません.']);
        }

        // 特記事項データの取得
        $prData = DB::table('job_supplement_info')
            ->where('order_code', $orderCode)
            ->first();

        // $jobオブジェクトにPRフィールドを追加する
        $job->pr_title1 = $prData->pr_title1 ?? '';
        $job->pr_contents1 = $prData->pr_contents1 ?? '';
        $job->pr_title2 = $prData->pr_title2 ?? '';
        $job->pr_contents2 = $prData->pr_contents2 ?? '';
        $job->pr_title3 = $prData->pr_title3 ?? '';
        $job->pr_contents3 = $prData->pr_contents3 ?? '';

        $checkedSupplementFlags = [];
        if ($prData) {
            // supplement_flagsフィールドから「key」のみを抽出します。
            $checkboxOptions = $this->checkboxOptions(); // すべてのチェックボックスを取得
            foreach ($checkboxOptions as $key => $label) {
                if (!empty($prData->{$key})) {
                    $checkedSupplementFlags[] = $key;
                }
            }
        }
        $licensesRaw = DB::table('job_license')
            ->where('order_code', $orderCode)
            ->get()
            ->toArray();

        // `job_license` jadvalidan ma'lumotlarni olish
        $licenses = DB::table('job_license as jl')
            ->leftJoin('master_license as ml', function ($join) {
                $join->on('jl.group_code', '=', 'ml.group_code')
                    ->on('jl.category_code', '=', 'ml.category_code')
                    ->on('jl.code', '=', 'ml.code');
            })
            ->where('jl.order_code', $orderCode)
            ->select(
                'jl.group_code',
                'jl.category_code',
                'jl.code',
                DB::raw('MAX(ml.category_name) as category_name'),
                DB::raw('MAX(ml.name) as name')
            )
            ->groupBy('jl.group_code', 'jl.category_code', 'jl.code')
            ->get()
            ->toArray();



        // Guruh va kategoriyalarni olish
        $groups = DB::table('master_license')
            ->select('group_code', 'group_name')
            ->distinct()
            ->get();

        $licenseCategories = DB::table('master_license')
            ->select('category_code', 'category_name')
            ->distinct()
            ->get();

        // 仕事に必要なスキルを身につける
        $skills = DB::table('job_skill')
            ->where('order_code', $orderCode)
            ->get();
        // job_noteデータを取得する
        $jobNoteData = DB::table('job_note')
            ->where('order_code', $orderCode)
            ->where('category_code', 'Note') // 静的値 'Note'
            ->where('code', 'BestMatch')    // 静的値 'BestMatch'
            ->first();
        // 追加フラグの情報（チェックボックス）
        $checkboxOptions = $this->checkboxOptions();
        // 勤務形態データ (OrderType) を取得**
        $orderTypes = DB::table('master_code')
            ->where('category_code', 'OrderType')
            ->whereIn('code', [1, 2, 3]) // 値1、2、3のみを取得する
            ->select('code', 'detail')
            ->get();

        $jobTypes = DB::table('job_job_type')
            ->where('order_code', $orderCode)
            ->get();

        $selectedBigClassCodes = [];
        $selectedMiddleClassCodes = [];
        $ids = [];

        foreach ($jobTypes as $jobType) {
            $selectedBigClassCodes[] = substr($jobType->job_type_code, 0, 2);
            $selectedMiddleClassCodes[] = substr($jobType->job_type_code, 2, 2);
            $ids[] = $jobType->id;
        }
        // always ensure 3 job type slots
        $selectedBigClassCodes = array_pad($selectedBigClassCodes, 3, '');
        $selectedMiddleClassCodes = array_pad($selectedMiddleClassCodes, 3, '');
        $ids = array_pad($ids, 3, null);


        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();

        $jobCategories = DB::table('master_job_type')
            ->select('big_class_code', 'middle_class_code as code', 'middle_clas_name as detail')
            ->get();


        // 📌 ユーザーが選択した都道府県
        $selectedPrefectures = DB::table('job_working_place')
            ->where('order_code', $orderCode)
            ->pluck('prefecture_code')
            ->toArray(); // toArray() 1列のみ含む
        $workingPlaces = DB::table('job_working_place')
            ->where('order_code', $orderCode)
            ->get(); // get() 複数の列を含む

        // `job_working_condition` jadvalidan ma'lumotlarni olish
        $jobWorkingCondition = DB::table('job_working_condition')
            ->where('order_code', $orderCode)
            ->orderBy('id', 'desc')
            ->first();

        // 📌 都道府県一覧
        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->select('code', 'detail')
            ->get();

        $academicOptions = DB::table('master_code')
            ->where('category_code', 'SchoolType')
            ->select('code', 'detail')
            ->get();
        // job_scheduled_to_intraduntion テーブルからデータを取得する
        $scheduledData = DB::table('job_scheduled_to_intraduntion')
            ->where('order_code', $orderCode)
            ->first();

        // 以前に保存したデータを取得する
        $jobSkills = DB::table('job_skill')
            ->where('order_code', $orderCode)
            ->get()
            ->groupBy('category_code'); // カテゴリ別にグループ化

        // カテゴリーの定義
        $categories = [
            'OS' => 'オペレーションシステム',
            'Application' => 'アプリケーション',
            'DevelopmentLanguage' => '開発言語',
            'Database' => 'データベース',
        ];

        return view('jobs.job_edit', compact(
            'job',
            'prData',
            'checkedSupplementFlags',
            'checkboxOptions',
            'licenses',
            'groups',
            'licenseCategories',
            'categories',
            'jobSkills',
            'checkboxOptions',
            'orderTypes',
            'selectedBigClassCodes',
            'selectedMiddleClassCodes',
            'bigClasses',
            'jobCategories',
            'ids',
            'orderCode',
            'selectedPrefectures',
            'workingPlaces',
            'prefectures',
            'academicOptions',
            'companyUser',
            'jobNoteData',
            'scheduledData',
            'jobWorkingCondition'
        ));
    }
    /**
     * 更新した作業を保存する
     */
    public function updateJob(Request $request, $orderCode)
    {
        // 1. 現在ログインしている企業のユーザーを取得
        $companyUser = Auth::guard('master_company')->user();
        $checkboxOptions = $this->checkboxOptions();

        if (!$companyUser) {
            return redirect()->route('company.login')->withErrors(['msg' => 'ログインが必要です']);
        }
        DB::enableQueryLog();
        Log::info("🔍 Starting Query Execution...");
        if ($request->input('work_update_flag') == '0') {
            $request->merge(['work_period' => null]);
        }        
        // データ確認
        try {
            $validatedData = $request->validate([
                'order_type' => 'nullable|exists:master_code,code',
                'order_progress_type' => 'nullable|in:1,2',
                'public_flag' => 'nullable|boolean',
                'job_type_detail' => 'nullable|string|max:1000',
                'big_class_code' => 'nullable|array|min:1',
                'big_class_code.*' => 'nullable|exists:master_job_type,big_class_code',
                'job_category' => 'nullable|array|min:1',
                'job_category.*' => 'nullable|exists:master_job_type,middle_class_code',
                'pr_title1' => 'nullable|string',
                'pr_contents1' => 'required|string|max:1000',
                'pr_title2' => 'nullable|string',
                'pr_contents2' => 'nullable|string|max:1000',
                'pr_title3' => 'nullable|string',
                'pr_contents3' => 'nullable|string|max:1000',
                'business_detail' => 'nullable|string',
                'BestMatch' => 'nullable|string|max:1000',
                'public_limit_day' => 'nullable|regex:/^\d{8}$/',
                'company_speciality' => 'nullable|string|max:1000',
                'catch_copy' => 'nullable|string|max:1000',
                'biz_name1' => 'nullable|string|max:1000',
                'biz_percentage1' => 'nullable|numeric|min:0|max:100',
                'biz_name2' => 'nullable|string|max:1000',
                'biz_percentage2' => 'nullable|numeric|min:0|max:100',
                'biz_name3' => 'nullable|string|max:1000',
                'biz_percentage3' => 'nullable|numeric|min:0|max:100',
                'biz_name4' => 'nullable|string|max:1000',
                'biz_percentage4' => 'nullable|numeric|min:0|max:100',
                'yearly_income_min' => 'nullable|numeric|min:0',
                'yearly_income_max' => 'nullable|numeric|gte:yearly_income_min',
                'monthly_income_min' => 'nullable|numeric|min:0',
                'monthly_income_max' => 'nullable|numeric|gte:monthly_income_min',
                'hourly_income_min' => 'nullable|numeric|min:0',
                'hourly_income_max' => 'nullable|numeric|gte:hourly_income_min',
                'daily_income_min' => 'nullable|numeric|min:0',
                'daily_income_max' => 'nullable|numeric|gte:daily_income_min',
                'income_remark' => 'nullable|string|max:1000',
                'employment_start_day' => ['nullable', 'regex:/^\d{8}$/'],
                'work_start_day' => ['nullable', 'regex:/^\d{8}$/'],
                'work_end_day' => ['nullable', 'regex:/^\d{8}$/'],
                'work_update_flag' => 'nullable|in:0,1',
                // 'work_period' => ['nullable', 'numeric', 'min:1'],
                'work_period' => [
                    'nullable',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->input('work_update_flag') == '1' && ($value === null || $value < 1)) {
                            $fail('更新ありの場合、更新有無は1以上である必要があります。');
                        }
                    },
                ],
                'work_start_time' => 'nullable|regex:/^\d{4}$/',
                'Work_end_time' => 'nullable|regex:/^\d{4}$/',
                'rest_start_time' => 'nullable|regex:/^\d{4}$/',
                'rest_end_time' => 'nullable|regex:/^\d{4}$/',
                'over_work_flag' => 'nullable|boolean',
                'work_time_remark' => 'nullable|string|max:1000',
                'weekly_holiday_type' => 'nullable|string|in:001,002,003,004,999',
                'holiday_remark' => 'nullable|string|max:1000',
                'prefecture_code' => 'nullable|array|min:1',
                'prefecture_code.*' => 'exists:job_working_place,prefecture_code',
                'city' => 'nullable|string|max:1000',
                'town' => 'nullable|string|max:1000',
                'address' => 'nullable|string|max:1000',
                'section' => 'nullable|string|max:1000',
                'telephone_number' => 'nullable|string|max:15',
                'charge_person_post' => 'nullable|string|max:1000',
                'charge_person_name' => 'nullable|string|max:1000',
                'age_min' => 'nullable|integer|min:0',
                'age_max' => 'nullable|integer|gte:age_min',
                'age_reason_flag' => 'nullable|in:K,L,M,N,O,P',
                'qualifications' => 'nullable|array',
                'qualifications.*.group_code' => 'nullable|exists:master_license,group_code',
                'qualifications.*.category_code' => 'nullable|exists:master_license,category_code',
                'qualifications.*.code' => 'nullable|exists:master_license,code',
                'hope_school_history_code' => 'nullable|exists:master_code,code',
                'graduation_year' => 'nullable|integer|digits:4|min:1900',
                'new_graduate_flag' => 'nullable|boolean',
                'employee_restaurant_flag' => 'nullable|boolean',
                'board_flag' => 'nullable|boolean',
                'smoking_flag' => 'nullable|boolean',
                'smoking_area_flag' => 'nullable|boolean',
                'skills' => 'nullable|array',
                'skills.*' => 'nullable|array',
                'skills.*.*' => 'nullable|exists:master_code,code',
                'supplement_flags' => 'nullable|array',
                'supplement_flags.*' => 'nullable|in:' . implode(',', array_keys($checkboxOptions)),
                'process1' => 'nullable|string|max:1000',
                'process2' => 'nullable|string|max:1000',
                'process3' => 'nullable|string|max:1000',
                'process4' => 'nullable|string|max:1000',
                'employee_code' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
            // dd($e->errors());
        }

        try {
            DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
                // 1. job_order 更新
                DB::table('job_order')->updateOrInsert([
                    'order_code' => $orderCode,
                    'company_code' => $companyUser->company_code
                ], [
                    'public_limit_day' => $validatedData['public_limit_day']
                        ? Carbon::createFromFormat('Ymd', $validatedData['public_limit_day'])->format('Y-m-d H:i:s')
                        : '0000-00-00 00:00:00',
                    'order_type' => $validatedData['order_type'] ?? null,
                    'order_progress_type' => $validatedData['order_progress_type'] ?? null,
                    'public_flag' => $validatedData['public_flag'] ?? 0,
                    'business_detail' => $validatedData['business_detail'] ?? '',
                    'job_type_detail' => $validatedData['job_type_detail'] ?? '',
                    'income_remark' => $validatedData['income_remark'] ?? '',
                    'over_work_flag' => $validatedData['over_work_flag'] ?? 0,
                    'work_time_remark' => $validatedData['work_time_remark'] ?? '',
                    'holiday_remark' => $validatedData['holiday_remark'] ?? '',
                    'weekly_holiday_type' => $validatedData['weekly_holiday_type'] ?? '000',
                    'hope_school_history_code' => $validatedData['hope_school_history_code'] ?? null,
                    'employee_restaurant_flag' => $validatedData['employee_restaurant_flag'] ?? 0,
                    'smoking_flag' => $validatedData['smoking_flag'] ?? 0,
                    'smoking_area_flag' => $validatedData['smoking_area_flag'] ?? 0,
                    'board_flag' => $validatedData['board_flag'] ?? 0,
                    'yearly_income_min' => $validatedData['yearly_income_min'] ?? 0,
                    'yearly_income_max' => $validatedData['yearly_income_max'] ?? 0,
                    'monthly_income_min' => $validatedData['monthly_income_min'] ?? 0,
                    'monthly_income_max' => $validatedData['monthly_income_max'] ?? 0,
                    'hourly_income_min' => $validatedData['hourly_income_min'] ?? 0,
                    'hourly_income_max' => $validatedData['hourly_income_max'] ?? 0,
                    'daily_income_min' => $validatedData['daily_income_min'] ?? 0,
                    'daily_income_max' => $validatedData['daily_income_max'] ?? 0,
                    'age_min' => $validatedData['age_min'] ?? null,
                    'age_max' => $validatedData['age_max'] ?? null,
                    'age_reason_flag' => $validatedData['age_reason_flag'] ?? null,
                    'employee_code' => $validatedData['employee_code'] ?? '',
                    'public_day' => now(),
                    'update_at' => now(),
                ]);

                // 2. job_job_type
                DB::table('job_job_type')->where('order_code', $orderCode)->delete();
                $lastTypeId = 0;
                foreach ($validatedData['big_class_code'] ?? [] as $i => $big) {
                    $middle = $validatedData['job_category'][$i] ?? null;
                    if ($middle) {
                        DB::table('job_job_type')->insert([
                            'order_code' => $orderCode,
                            'id' => ++$lastTypeId,
                            'job_type_code' => $big . $middle . '000',
                            'created_at' => now(),
                            'update_at' => now(),
                        ]);
                    }
                }

                // 3. job_license
                DB::table('job_license')->where('order_code', $orderCode)->delete();
                $lastLicenseId = 0;
                foreach ($validatedData['qualifications'] ?? [] as $q) {
                    if (!empty($q['group_code']) && !empty($q['category_code']) && !empty($q['code'])) {
                        DB::table('job_license')->insert([
                            'order_code' => $orderCode,
                            'id' => ++$lastLicenseId,
                            'group_code' => $q['group_code'],
                            'category_code' => $q['category_code'],
                            'code' => $q['code'],
                            'created_at' => now(),
                            'update_at' => now(),
                        ]);
                    }
                }

                // 4. job_skill
                DB::table('job_skill')->where('order_code', $orderCode)->delete();
                $lastSkillId = 0;
                foreach ($validatedData['skills'] ?? [] as $cat => $skills) {
                    foreach ($skills as $code) {
                        DB::table('job_skill')->insert([
                            'order_code' => $orderCode,
                            'id' => ++$lastSkillId,
                            'category_code' => $cat,
                            'code' => $code,
                            'period' => 0,
                            'created_at' => now(),
                            'update_at' => now(),
                        ]);
                    }
                }

                // 5. job_working_place
                DB::table('job_working_place')->where('order_code', $orderCode)->delete();
                foreach ($validatedData['prefecture_code'] ?? [] as $idx => $pref) {
                    DB::table('job_working_place')->insert([
                        'order_code' => $orderCode,
                        'working_place_seq' => $idx + 1,
                        'area' => '日本',
                        'prefecture_code' => $pref,
                        'city' => $validatedData['city'] ?? '',
                        'town' => $validatedData['town'] ?? '',
                        'address' => $validatedData['address'] ?? '',
                        'section' => $validatedData['section'] ?? '',
                        'telephone_number' => $validatedData['telephone_number'] ?? '',
                        'charge_person_post' => $validatedData['charge_person_post'] ?? '',
                        'charge_person_name' => $validatedData['charge_person_name'] ?? '',
                    ]);
                }

                // 6. job_supplement_info
                $supplement = [
                    'order_code' => $orderCode,
                    'company_code' => $companyUser->company_code,
                    'process1' => $validatedData['process1'] ?? '',
                    'process2' => $validatedData['process2'] ?? '',
                    'process3' => $validatedData['process3'] ?? '',
                    'process4' => $validatedData['process4'] ?? '',
                    'company_speciality' => $validatedData['company_speciality'] ?? '',
                    'catch_copy' => $validatedData['catch_copy'] ?? '',
                    'pr_title1' => $validatedData['pr_title1'] ?? '',
                    'pr_title2' => $validatedData['pr_title2'] ?? '',
                    'pr_title3' => $validatedData['pr_title3'] ?? '',
                    'pr_contents1' => $validatedData['pr_contents1'] ?? '',
                    'pr_contents2' => $validatedData['pr_contents2'] ?? '',
                    'pr_contents3' => $validatedData['pr_contents3'] ?? '',
                    'biz_name1' => $validatedData['biz_name1'] ?? '',
                    'biz_name2' => $validatedData['biz_name2'] ?? '',
                    'biz_name3' => $validatedData['biz_name3'] ?? '',
                    'biz_name4' => $validatedData['biz_name4'] ?? '',
                    'biz_percentage1' => $validatedData['biz_percentage1'] ?? 0,
                    'biz_percentage2' => $validatedData['biz_percentage2'] ?? 0,
                    'biz_percentage3' => $validatedData['biz_percentage3'] ?? 0,
                    'biz_percentage4' => $validatedData['biz_percentage4'] ?? 0,
                    'created_at' => now(),
                    'update_at' => now(),
                ];
                foreach ((new static)->checkboxOptions() as $key => $label) {
                    $supplement[$key] = in_array($key, $validatedData['supplement_flags'] ?? []) ? 1 : 0;
                }
                DB::table('job_supplement_info')->updateOrInsert([
                    'order_code' => $orderCode,
                ], $supplement);

                // 7. job_note
                DB::table('job_note')->updateOrInsert([
                    'order_code' => $orderCode,
                    'category_code' => 'Note',
                    'code' => 'BestMatch',
                ], [
                    'note' => $validatedData['BestMatch'] ?? '',
                    'update_at' => now(),
                ]);

                // 8. job_scheduled_to_intraduntion
                DB::table('job_scheduled_to_intraduntion')->updateOrInsert([
                    'order_code' => $orderCode,
                ], [
                    'employment_start_day' => isset($validatedData['employment_start_day']) ? Carbon::createFromFormat('Ymd', $validatedData['employment_start_day'])->format('Y-m-d H:i:s') : null,
                    'work_start_day' => isset($validatedData['work_start_day']) ? Carbon::createFromFormat('Ymd', $validatedData['work_start_day'])->format('Y-m-d H:i:s') : null,
                    'work_end_day' => isset($validatedData['work_end_day']) ? Carbon::createFromFormat('Ymd', $validatedData['work_end_day'])->format('Y-m-d H:i:s') : null,
                    'work_period' => $validatedData['work_period'] ?? null,
                    'work_update_flag' => $validatedData['work_update_flag'] ?? 0,
                    'new_graduate_flag' => $validatedData['new_graduate_flag'] ?? 0,
                    'update_at' => now(),
                ]);                

                // 9. job_working_condition
                $lastCondId = DB::table('job_working_condition')
                    ->where('order_code', $orderCode)
                    ->max('id') ?? 0;
                DB::table('job_working_condition')->insert([
                    'order_code' => $orderCode,
                    'id' => ++$lastCondId,
                    'work_start_time' => $validatedData['work_start_time'],
                    'Work_end_time' => $validatedData['Work_end_time'],
                    'rest_start_time' => $validatedData['rest_start_time'],
                    'rest_end_time' => $validatedData['rest_end_time'],
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
            });
            Log::info('検証のために受信したデータ: ', $validatedData);
            // dd($request->all());

            // `order_type` を取得する
            $orderType = $validatedData['order_type'];
            $this->sendAgentNotification($orderCode, $orderType, 'update');
            Log::info('✅ 全ての求人票更新処理が完了しました。');
            return redirect()->route('jobs.job_list', ['order_code' => $orderCode])
                ->with('success', '更新が成功しました。');
        } catch (\Throwable $e) {
            Log::error('❌ 求人票更新エラー', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors(['error' => '更新中にエラーが発生しました。'])->withInput();
        }
    }
}
