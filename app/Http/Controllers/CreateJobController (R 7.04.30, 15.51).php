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
            // カテゴリ
            if (!empty($qualification['group_code'])) {
                $categoryOptions[$i] = DB::table('master_license')
                    ->select('category_code', 'category_name')
                    ->where('group_code', $qualification['group_code'])
                    ->distinct()
                    ->get()
                    ->toArray();
            }

            // 資格
            if (!empty($qualification['group_code']) && !empty($qualification['category_code'])) {
                $licenseOptions[$i] = DB::table('master_license')
                    ->select('code', 'name')
                    ->where('group_code', $qualification['group_code'])
                    ->where('category_code', $qualification['category_code'])
                    ->get()
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

        // 2. 入力データの検証
        $validatedData = $request->validate([
            'order_type' => 'nullable|exists:master_code,code',
            'order_progress_type' => 'nullable|in:1,2',
            'public_flag' => 'nullable|boolean',
            'job_type_detail' => 'nullable|string|max:255',
            'big_class_code' => 'nullable|array|min:1',
            'big_class_code.*' => 'nullable|exists:master_job_type,big_class_code',
            'job_category' => 'nullable|array|min:1',
            'job_category.*' => 'nullable|exists:master_job_type,middle_class_code',
            'pr_title1' => 'nullable|string',
            'pr_contents1' => 'required|string|max:255',
            'pr_title2' => 'nullable|string',
            'pr_contents2' => 'nullable|string|max:255',
            'pr_title3' => 'nullable|string',
            'pr_contents3' => 'nullable|string|max:255',
            'business_detail' => 'nullable|string',
            'BestMatch' => 'nullable|string|max:1000',
            'public_limit_day' => 'nullable|regex:/^\d{8}$/',
            // 'recruitment_limit_day' => 'nullable',
            'company_speciality' => 'nullable|string|max:255',
            'catch_copy' => 'nullable|string|max:255',
            'biz_name1' => 'nullable|string|max:255',
            'biz_percentage1' => 'nullable|numeric|min:0|max:100',
            'biz_name2' => 'nullable|string|max:255',
            'biz_percentage2' => 'nullable|numeric|min:0|max:100',
            'biz_name3' => 'nullable|string|max:255',
            'biz_percentage3' => 'nullable|numeric|min:0|max:100',
            'biz_name4' => 'nullable|string|max:255',
            'biz_percentage4' => 'nullable|numeric|min:0|max:100',
            'yearly_income_min' => 'required_if:order_type,2|numeric|min:0',
            'yearly_income_max' => 'required_if:order_type,2|numeric|gte:yearly_income_min',
            'monthly_income_min' => 'nullable|numeric|min:0',
            'monthly_income_max' => 'nullable|numeric|gte:monthly_income_min',
            'hourly_income_min' => 'nullable|numeric|min:0',
            'hourly_income_max' => 'nullable|numeric|gte:hourly_income_min',
            'daily_income_min' => 'nullable|numeric|min:0',
            'daily_income_max' => 'nullable|numeric|gte:daily_income_min',
            'income_remark' => 'nullable|string|max:255',
            'employment_start_day' => [
                'nullable', // デフォルトでNull可能
                'required_if:order_type,2', // order_typeが2の場合に必須
                'regex:/^\d{8}$/', // YYYYMMDDの形式をチェックします
            ],
            'work_start_day' => [
                'nullable', // デフォルトでNull可能
                'required_if:order_type,1,3', // order_type が 1 または 3 の場合にのみ必須です
                'regex:/^\d{8}$/', // YYYYMMDDの形式をチェックします
            ],
            'work_end_day' => [
                'nullable', // デフォルトでNull可能
                'required_if:order_type,1,3', // order_type が 1 または 3 の場合にのみ必須です
                'regex:/^\d{8}$/', // YYYYMMDDの形式をチェックします
            ],
            'work_update_flag' => 'nullable|in:0,1', // 0または1の値を取る
            'work_period' => [
                'nullable', // デフォルトでNull可能
                'required_if:work_update_flag,1', // workUpdateFlag が 1 の場合にのみ必要
                'numeric', // 数字のみにしてください。
                'min:1', // 少なくとも1つ必要です
            ],

            'work_start_time' => 'nullable|regex:/^\d{4}$/',
            'Work_end_time' => 'nullable|regex:/^\d{4}$/',
            'rest_start_time' => 'nullable|regex:/^\d{4}$/',
            'rest_end_time' => 'nullable|regex:/^\d{4}$/',
            'over_work_flag' => 'nullable|boolean',
            'work_time_remark' => 'nullable|string|max:255',
            'weekly_holiday_type' => 'nullable|string|in:001,002,003,004,999',
            'holiday_remark' => 'nullable|string|max:255',
            'prefecture_code' => 'nullable|array|min:1',
            'prefecture_code.*' => 'exists:job_working_place,prefecture_code',
            'city' => 'nullable|string|max:255',
            'town' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'telephone_number' => 'nullable|string|max:15',
            'charge_person_post' => 'nullable|string|max:255',
            'charge_person_name' => 'nullable|string|max:255',
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
            'supplement_flags.*' => 'nullable|in:' . implode(',', array_keys($checkboxOptions)), // チェックボックスオプション内のキーをチェックします
            'process1' => 'nullable|string|max:255',
            'process2' => 'nullable|string|max:255',
            'process3' => 'nullable|string|max:255',
            'process4' => 'nullable|string|max:255',

        ]);
        Log::info('Checkbox options:', $checkboxOptions);
        // dd($validatedData);
        // dd($validatedData);

        // 3. order_code を生成する
        $lastOrderCode = DB::table('job_order')
            ->orderByRaw('CAST(SUBSTRING(order_code, 2) AS UNSIGNED) DESC')
            ->value('order_code');

        $nextId = $lastOrderCode ? intval(substr($lastOrderCode, 1)) + 1 : 1;
        $orderCode = 'J' . str_pad($nextId, 7, '0', STR_PAD_LEFT);

        // 4. job_orderデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser, $request) {
            $jobData = [
                'order_code' => $orderCode,
                'company_code' => $companyUser->company_code,
                'public_limit_day' => $validatedData['public_limit_day']
                    ? Carbon::createFromFormat('Ymd', $validatedData['public_limit_day'])->format('Y-m-d H:i:s')
                    : '0000-00-00 00:00:00',
                // 'recruitment_limit_day' => isset($validatedData['recruitment_limit_day']) && $validatedData['recruitment_limit_day']
                //     ? Carbon::createFromFormat('Ymd', $validatedData['recruitment_limit_day'])->format('Y-m-d H:i:s')
                //     : '0000-00-00 00:00:00',
                'order_type' => $validatedData['order_type'],
                'order_progress_type' => $validatedData['order_progress_type'],
                'public_flag' => $validatedData['public_flag'],
                'business_detail' => $validatedData['business_detail'],
                'job_type_detail' => $validatedData['job_type_detail'],
                'income_remark' => $validatedData['income_remark'],
                'over_work_flag' => $validatedData['over_work_flag'],
                'work_time_remark' => $validatedData['work_time_remark'],
                'holiday_remark' => $validatedData['holiday_remark'] ?? 0,
                'weekly_holiday_type' => $validatedData['weekly_holiday_type'] ?? '000',
                'hope_school_history_code' => $validatedData['hope_school_history_code'],
                'employee_restaurant_flag' => $validatedData['employee_restaurant_flag'] ?? 0,
                'smoking_flag' => $validatedData['smoking_flag'] ?? 0,
                'smoking_area_flag' => $validatedData['smoking_area_flag'] ?? 0,
                'board_flag' => $validatedData['board_flag'] ?? 0,
                'public_day' => now(),
                'created_at' => now(),
                'update_at' => now(),
            ];

            // order_typeに基づいて収益データを追加する
            if (!empty($validatedData['order_type'])) {
                if ($validatedData['order_type'] == 2) {
                    // 年収 + 月給 saqlanadi
                    $jobData['yearly_income_min'] = $validatedData['yearly_income_min'] ?? 0;
                    $jobData['yearly_income_max'] = $validatedData['yearly_income_max'] ?? 0;
                    $jobData['monthly_income_min'] = $validatedData['monthly_income_min'] ?? 0;
                    $jobData['monthly_income_max'] = $validatedData['monthly_income_max'] ?? 0;
                } else {
                    // それ以外は 0 で初期化
                    $jobData['yearly_income_min'] = 0;
                    $jobData['yearly_income_max'] = 0;
                    $jobData['monthly_income_min'] = 0;
                    $jobData['monthly_income_max'] = 0;
                }
            }

            // 利用可能な場合のみ age_min と age_max データを追加します
            if (!empty($validatedData['age_min'])) {
                $jobData['age_min'] = $validatedData['age_min'];
            }

            if (!empty($validatedData['age_max'])) {
                $jobData['age_max'] = $validatedData['age_max'];
            }

            // age_reason_flag フィールドの必須の保存
            $jobData['age_reason_flag'] = $validatedData['age_reason_flag'] ?? null;

            // job_orderテーブルにデータを保存する
            // dd($jobData);

            DB::table('job_order')->insert($jobData);
        });

        // job_job_typeデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode) {
            $jobTypeData = [];
            $lastId = DB::table('job_job_type')
                ->where('order_code', $orderCode)
                ->max('id');
            $lastId = $lastId ? $lastId : 0; // max('id') が null の場合、0 であるとみなします。

            foreach ($validatedData['big_class_code'] as $key => $bigClassCode) {
                $middleClassCode = $validatedData['job_category'][$key] ?? null;

                if ($middleClassCode) {
                    $smallClassCode = '000'; // 値は常に「000」に設定します

                    // データ収集
                    $jobTypeData[] = [
                        'order_code' => $orderCode,
                        'id' => ++$lastId, // IDを毎回増加
                        'job_type_code' => $bigClassCode . $middleClassCode . $smallClassCode,
                        'created_at' => now(),
                        'update_at' => now(),
                    ];
                }
            }

            // `job_job_type` テーブルにデータを挿入する
            if (!empty($jobTypeData)) {
                DB::table('job_job_type')->insert($jobTypeData);
            }
        });

        // job_licenseデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode) {
            $qualifications = $validatedData['qualifications'] ?? []; // ユーザーが投稿した情報
            // テーブル内の最後のIDをorder_codeで決定する
            $lastId = DB::table('job_license')
                ->where('order_code', $orderCode)
                ->max('id') ?? 0; // 情報が利用できない場合は 0 とみなされます。

            $insertData = [];
            foreach ($qualifications as $qualification) {
                // 完全に記入された資格情報のみを追加してください。
                if (!empty($qualification['group_code']) || !empty($qualification['category_code']) || !empty($qualification['code'])) {
                    $insertData[] = [
                        'order_code' => $orderCode,
                        'id' => ++$lastId,
                        'group_code' => $qualification['group_code'] ?? null,
                        'category_code' => $qualification['category_code'] ?? null,
                        'code' => $qualification['code'] ?? null,
                        'created_at' => now(),
                        'update_at' => now(),
                    ];
                }
            }

            // 資格データが存在する場合は保存します。
            if (!empty($insertData)) {
                DB::table('job_license')->insert($insertData);
            }
        });

        // job_skillデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode) {
            // テーブル内の最後のIDをorder_codeで決定する
            $lastId = DB::table('job_skill')
                ->where('order_code', $orderCode)
                ->max('id') ?? 0; // 情報が利用できない場合は 0 とみなされます。
            if (!empty($validatedData['skills'])) {
                $jobSkillData = [];

                foreach ($validatedData['skills'] as $categoryCode => $skills) {
                    foreach ($skills as $skillCode) {
                        $jobSkillData[] = [
                            'order_code' => $orderCode,
                            'id' => ++$lastId,
                            'category_code' => $categoryCode,
                            'code' => $skillCode,
                            'period' => 0, // 必要に応じて動的な値を追加する
                            'created_at' => now(),
                            'update_at' => now(),
                        ];
                    }
                }
                // dd($jobSkillData);

                // データベースエントリ
                if (!empty($jobSkillData)) {
                    DB::table('job_skill')->insert($jobSkillData);
                }
            }
        });

        // job_supplement_infoデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
            // 基本情報
            $insertData = [
                'order_code' => $orderCode,
                'company_code' => $companyUser->company_code,
                'process1' => $validatedData['process1'] ?? null,
                'process2' => $validatedData['process2'] ?? null,
                'process3' => $validatedData['process3'] ?? null,
                'process4' => $validatedData['process4'] ?? null,
                'company_speciality' => $validatedData['company_speciality'] ?? null,
                'catch_copy' => $validatedData['catch_copy'] ?? null,
                'pr_title1' => $validatedData['pr_title1'] ?? null,
                'pr_title2' => $validatedData['pr_title2'] ?? null,
                'pr_title3' => $validatedData['pr_title3'] ?? null,
                'pr_contents1' => $validatedData['pr_contents1'] ?? null,
                'pr_contents2' => $validatedData['pr_contents2'] ?? null,
                'pr_contents3' => $validatedData['pr_contents3'] ?? null,
                'biz_name1' => $validatedData['biz_name1'] ?? 0,
                'biz_name2' => $validatedData['biz_name2'] ?? 0,
                'biz_name3' => $validatedData['biz_name3'] ?? 0,
                'biz_name4' => $validatedData['biz_name4'] ?? 0,
                'biz_percentage1' => $validatedData['biz_percentage1'] ?? null,
                'biz_percentage2' => $validatedData['biz_percentage2'] ?? null,
                'biz_percentage3' => $validatedData['biz_percentage3'] ?? null,
                'biz_percentage4' => $validatedData['biz_percentage4'] ?? null,
                'created_at' => now(),
                'update_at' => now(),
            ];

            // checkboxOptions によるフラグの動的な追加
            $checkboxOptions = $this->checkboxOptions();
            foreach ($checkboxOptions as $key => $label) {
                $insertData[$key] = in_array($key, $validatedData['supplement_flags'] ?? []) ? 1 : 0;
            }
            // dd($insertData);
            // `job_supplement_info` テーブルに書き込む
            DB::table('job_supplement_info')->insert($insertData);
        });

        // job_noteデータを保存する
        DB::transaction(function () use ($request, $orderCode) {
            // 挿入するデータを準備する
            $jobNoteData = [
                'order_code' => $orderCode,
                'category_code' => 'Note', // 例に従った静的値
                'code' => 'BestMatch',    // 例に従った静的値
                'note' => $request->input('BestMatch'), // テキストエリアからの入力
                'created_at' => now(),
                'update_at' => now(),
            ];
            // dd($jobNoteData);
            // job_noteテーブルに挿入
            DB::table('job_note')->insert($jobNoteData);
        });

        // job_scheduled_to_intraduntionデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode) {
            // 1. $validatedDataからのフォーマットされた値
            $formattedEmploymentStartDay = $validatedData['employment_start_day']
                ? Carbon::createFromFormat('Ymd', $validatedData['employment_start_day'])->format('Y-m-d H:i:s')
                : null;

            $formattedWorkStartDay = $validatedData['work_start_day']
                ? Carbon::createFromFormat('Ymd', $validatedData['work_start_day'])->format('Y-m-d H:i:s')
                : null;

            $formattedWorkEndDay = $validatedData['work_end_day']
                ? Carbon::createFromFormat('Ymd', $validatedData['work_end_day'])->format('Y-m-d H:i:s')
                : null;

            // 2. データ準備
            $insertData = [
                'order_code' => $orderCode,
                'employment_start_day' => $formattedEmploymentStartDay,
                'work_start_day' => $formattedWorkStartDay,
                'work_end_day' => $formattedWorkEndDay,
                'work_period' => $validatedData['work_period'] ?? 0,
                'work_update_flag' => $validatedData['work_update_flag'] ?? 0,
                'new_graduate_flag' => $validatedData['new_graduate_flag'] ?? 0,
                'created_at' => now(),
                'update_at' => now(),
            ];
            // dd($insertData);
            // 3. `job_scheduled_to_internship` に挿入
            DB::table('job_scheduled_to_intraduntion')->insert($insertData);
        });

        // job_working_conditionデータを保存する
        // work_start_time & work_end_time - rest_start_time & rest_end_time
        DB::transaction(function () use ($validatedData, $orderCode) {
            // テーブル内の最後のIDをorder_codeで決定する
            $lastId = DB::table('job_working_condition')
                ->where('order_code', $orderCode)
                ->max('id') ?? 0; // 情報が利用できない場合は 0 とみなされます。
            $insertData = [
                'order_code' => $orderCode,
                'id' => ++$lastId,
                'work_start_time' => $validatedData['work_start_time'] ?? null,
                'Work_end_time' => $validatedData['Work_end_time'] ?? null,
                'rest_start_time' => $validatedData['rest_start_time'] ?? null,
                'rest_end_time' => $validatedData['rest_end_time'] ?? null,
                'created_at' => now(),
                'update_at' => now(),
            ];
            // dd($insertData);

            // `job_working_condition` テーブルに書き込む
            DB::table('job_working_condition')->insert($insertData);
        });

        // job_working_placeデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode) {
            $insertData = [];

            foreach ($validatedData['prefecture_code'] as $key => $prefectureCode) {
                $insertData[] = [
                    'order_code' => $orderCode,
                    'working_place_seq' => $key + 1,
                    'area' => '日本',
                    'prefecture_code' => $prefectureCode,
                    'city' => $validatedData['city'] ?? null,
                    'town' => $validatedData['town'] ?? null,
                    'address' => $validatedData['address'] ?? null,
                    'section' => $validatedData['section'] ?? null,
                    'telephone_number' => $validatedData['telephone_number'] ?? null,
                    'charge_person_post' => $validatedData['charge_person_post'] ?? null,
                    'charge_person_name' => $validatedData['charge_person_name'] ?? null,
                ];
            }
            // dd($insertData);

            // `job_working_place`テーブルに書き込む
            DB::table('job_working_place')->insert($insertData);
        });
        // 5. メールを送信
        // `order_type` を取得する
        $orderType = $validatedData['order_type'];
        $this->sendAgentNotification($orderCode, $orderType, 'create');

        // return redirect()->back()->with('success', '求人票が正常に作成されました！');
        return redirect()->route('jobs.job_list')->with('success', '新しい求人票作成されました');
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

        // `job_license` jadvalidan ma'lumotlarni olish
        $licenses = DB::table('job_license')
            ->join('master_license', function ($join) {
                $join->on('job_license.group_code', '=', 'master_license.group_code')
                    ->on('job_license.category_code', '=', 'master_license.category_code')
                    ->on('job_license.code', '=', 'master_license.code');
            })
            ->where('job_license.order_code', $orderCode)
            ->select(
                'job_license.group_code',
                'job_license.category_code',
                'job_license.code',
                'master_license.category_name',
                'master_license.name'
            )
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
        // **勤務形態データ (OrderType) を取得**
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
        // データ確認


        try {
            $validatedData = $request->validate([
                'order_type' => 'nullable|exists:master_code,code',
                'order_progress_type' => 'nullable|in:1,2',
                'public_flag' => 'nullable|boolean',
                'job_type_detail' => 'nullable|string|max:255',
                'big_class_code' => 'nullable|array',
                'big_class_code.*' => 'nullable|exists:master_job_type,big_class_code',
                'middle_class_code' => 'nullable|array',
                'middle_class_code.*' => 'nullable|exists:master_job_type,middle_class_code',
                'pr_title1' => 'nullable|string',
                'pr_contents1' => 'nullable|string|max:255',
                'pr_title2' => 'nullable|string',
                'pr_contents2' => 'nullable|string|max:255',
                'pr_title3' => 'nullable|string',
                'pr_contents3' => 'nullable|string|max:255',
                'business_detail' => 'nullable|string',
                'BestMatch' => 'nullable|string|max:1000',
                'public_limit_day' => [
                    'nullable',
                    'regex:/^\d{8}$/', // YYYYMMDD formatini tekshiradi
                ],
                // 'recruitment_limit_day' => 'nullable|regex:/^\d{8}$/', // YYYYMMDD formatini tekshirish
                'company_speciality' => 'nullable|string|max:255',
                'catch_copy' => 'nullable|string|max:255',
                'biz_name1' => 'nullable|string|max:255',
                'biz_percentage1' => 'nullable|numeric|min:0|max:100',
                'biz_name2' => 'nullable|string|max:255',
                'biz_percentage2' => 'nullable|numeric|min:0|max:100',
                'biz_name3' => 'nullable|string|max:255',
                'biz_percentage3' => 'nullable|numeric|min:0|max:100',
                'biz_name4' => 'nullable|string|max:255',
                'biz_percentage4' => 'nullable|numeric|min:0|max:100',
                'monthly_income_min' => 'nullable|integer',
                'monthly_income_max' => 'nullable|integer',
                'yearly_income_min' => 'nullable|integer',
                'yearly_income_max' => 'nullable|integer',
                'income_remark' => 'nullable|string|max:255',
                'employment_start_day' => 'nullable|regex:/^\d{8}$/',
                'work_start_day' => 'nullable|regex:/^\d{8}$/',
                'work_end_day' => 'nullable|regex:/^\d{8}$/',
                'work_update_flag' => 'nullable|in:0,1',
                'work_period' => 'nullable|numeric',
                'work_start_time' => 'nullable|regex:/^\d{4}$/',
                'Work_end_time' => 'nullable|regex:/^\d{4}$/',
                'rest_start_time' => 'nullable|regex:/^\d{4}$/',
                'rest_end_time' => 'nullable|regex:/^\d{4}$/',
                'over_work_flag' => 'nullable|boolean',
                'work_time_remark' => 'nullable|string|max:255',
                'weekly_holiday_type' => 'nullable|string|in:001,002,003,004,999',
                'holiday_remark' => 'nullable|string|max:255',
                'prefecture_code' => 'nullable|array',
                'prefecture_code.*' => 'nullable|exists:job_working_place,prefecture_code',
                'city.*' => 'nullable|string',
                'town.*' => 'nullable|string',
                'address.*' => 'nullable|string',
                'section.*' => 'nullable|string',
                'telephone_number.*' => 'nullable|string|max:15',
                'charge_person_post.*' => 'nullable|string',
                'charge_person_name.*' => 'nullable|string',
                'age_min' => 'nullable|integer|min:0',
                'age_max' => 'nullable|integer|min:0',
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
                'process1' => 'nullable|string|max:255',
                'process2' => 'nullable|string|max:255',
                'process3' => 'nullable|string|max:255',
                'process4' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
            // dd($e->errors());
        }

        // 1. job_orderデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
            // 以前のデータを取得する
            $existingJob = DB::table('job_order')->where('order_code', $orderCode)->first();

            // 変更がない場合はアップデートを続行します。
            if (!$existingJob && empty(array_filter($validatedData))) {
                return;
            }

            $jobData = [
                'order_code' => $orderCode,
                'company_code' => $companyUser->company_code,
                'public_limit_day' => !empty($validatedData['public_limit_day'])
                    ? Carbon::createFromFormat('Ymd', $validatedData['public_limit_day'])->format('Y-m-d H:i:s')
                    : '0000-00-00 00:00:00',
                'order_type' => $validatedData['order_type'] ?? $existingJob->order_type ?? null,
                'order_progress_type' => $validatedData['order_progress_type'] ?? $existingJob->order_progress_type ?? null,
                'public_flag' => $validatedData['public_flag'] ?? $existingJob->public_flag ?? 0,
                'business_detail' => $validatedData['business_detail'] ?? $existingJob->business_detail ?? null,
                'job_type_detail' => $validatedData['job_type_detail'] ?? $existingJob->job_type_detail ?? null,
                'income_remark' => $validatedData['income_remark'] ?? $existingJob->income_remark ?? null,
                'over_work_flag' => $validatedData['over_work_flag'] ?? $existingJob->over_work_flag ?? 0,
                'work_time_remark' => $validatedData['work_time_remark'] ?? $existingJob->work_time_remark ?? null,
                'holiday_remark' => $validatedData['holiday_remark'] ?? $existingJob->holiday_remark ?? 0,
                'weekly_holiday_type' => $validatedData['weekly_holiday_type'] ?? $existingJob->weekly_holiday_type ?? null,
                'hope_school_history_code' => $validatedData['hope_school_history_code'] ?? $existingJob->hope_school_history_code ?? null,
                'employee_restaurant_flag' => $validatedData['employee_restaurant_flag'] ?? $existingJob->employee_restaurant_flag ?? 0,
                'smoking_flag' => $validatedData['smoking_flag'] ?? $existingJob->smoking_flag ?? 0,
                'smoking_area_flag' => $validatedData['smoking_area_flag'] ?? $existingJob->smoking_area_flag ?? 0,
                'board_flag' => $validatedData['board_flag'] ?? $existingJob->board_flag ?? 0,
                'public_day' => now(),
                'update_at' => now(),
            ];

            // order_type に基づいて収益データを追加する
            if (!empty($validatedData['order_type'])) {
                if ($validatedData['order_type'] == 2) {
                    // 年収 + 月給
                    $jobData['yearly_income_min'] = $validatedData['yearly_income_min'] ?? 0;
                    $jobData['yearly_income_max'] = $validatedData['yearly_income_max'] ?? 0;
                    $jobData['monthly_income_min'] = $validatedData['monthly_income_min'] ?? 0;
                    $jobData['monthly_income_max'] = $validatedData['monthly_income_max'] ?? 0;

                    $jobData['daily_income_min'] = 0;
                    $jobData['daily_income_max'] = 0;
                    $jobData['hourly_income_min'] = 0;
                    $jobData['hourly_income_max'] = 0;
                } elseif ($validatedData['order_type'] == 1) {
                    // 日給
                    $jobData['daily_income_min'] = $validatedData['daily_income_min'] ?? 0;
                    $jobData['daily_income_max'] = $validatedData['daily_income_max'] ?? 0;

                    $jobData['yearly_income_min'] = 0;
                    $jobData['yearly_income_max'] = 0;
                    $jobData['monthly_income_min'] = 0;
                    $jobData['monthly_income_max'] = 0;
                    $jobData['hourly_income_min'] = 0;
                    $jobData['hourly_income_max'] = 0;
                } elseif ($validatedData['order_type'] == 3) {
                    // 時給
                    $jobData['hourly_income_min'] = $validatedData['hourly_income_min'] ?? 0;
                    $jobData['hourly_income_max'] = $validatedData['hourly_income_max'] ?? 0;

                    $jobData['yearly_income_min'] = 0;
                    $jobData['yearly_income_max'] = 0;
                    $jobData['monthly_income_min'] = 0;
                    $jobData['monthly_income_max'] = 0;
                    $jobData['daily_income_min'] = 0;
                    $jobData['daily_income_max'] = 0;
                }
            }

            // 利用可能な場合のみ age_min と age_max データを追加します
            if (!empty($validatedData['age_min'])) {
                $jobData['age_min'] = isset($validatedData['age_min']) ? $validatedData['age_min'] : 0;
            }

            if (!empty($validatedData['age_max'])) {
                $jobData['age_max'] = isset($validatedData['age_max']) ? $validatedData['age_max'] : 0;
            }

            // age_reason_flag フィールドの必須の保存
            $jobData['age_reason_flag'] = $validatedData['age_reason_flag'] ?? $existingJob->age_reason_flag ?? 0;

            // job_orderテーブルのデータを更新または挿入する
            DB::table('job_order')->updateOrInsert(
                [
                    'order_code' => $orderCode,
                    'company_code' => $companyUser->company_code // この会社のこの注文コードのみが更新されます
                ],
                $jobData
            );
            // dd([
            //     'monthly_income_min' => $validatedData['monthly_income_min'] ?? 'not set',
            //     'monthly_income_max' => $validatedData['monthly_income_max'] ?? 'not set',
            //     'jobData' => $jobData
            // ]);

        });

        // 2. job_job_typeデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
            // `order_code` が `company_code` と一致するかどうかを確認します
            $companyCode = DB::table('job_order')
                ->where('order_code', $orderCode)
                ->where('company_code', $companyUser->company_code) // この会社にのみ所属する必要があります
                ->value('company_code');

            if (!$companyCode) {
                Log::warning("❌ この注文コードは会社に属していません: {$orderCode}");
                return; // 企業が適応しなければ何も変わりません。
            }

            // 同じ `order_code` に対して利用可能な `job_type_code`s` を (ID とともに) 取得します。
            $existingRecords = DB::table('job_job_type')
                ->where('order_code', $orderCode)
                ->select('id', 'job_type_code')
                ->get()
                ->keyBy('job_type_code'); // `job_type_code`でインデックスされた配列を作成します。

            // 🔵 **この `order_code` の最大の `id` を取得します** (そうでない場合は `0`)
            $lastId = DB::table('job_job_type')
                ->where('order_code', $orderCode)
                ->max('id') ?? 0;

            // ✅ **更新された `job_type_code` を準備しています**
            $newJobTypeCodes = [];
            $insertData = [];
            $usedJobTypeCodes = [];

            foreach ($validatedData['big_class_code'] as $key => $bigClassCode) {
                $middleClassCode = $validatedData['middle_class_code'][$key] ?? null;

                if ($middleClassCode) {
                    $smallClassCode = '000';
                    $newJobTypeCode = $bigClassCode . $middleClassCode . $smallClassCode;
                    $newJobTypeCodes[] = $newJobTypeCode;

                    // 🔄 **`job_type_code` が存在する場合 – 更新**
                    if (isset($existingRecords[$newJobTypeCode])) {
                        DB::table('job_job_type')
                            ->where('id', $existingRecords[$newJobTypeCode]->id) // ✅ これはまさに`id`です
                            ->where('order_code', $orderCode) // ✅ この`order_code`のみ
                            ->update([
                                'job_type_code' => $newJobTypeCode,
                                'update_at' => now(),
                            ]);
                        $usedJobTypeCodes[] = $newJobTypeCode;
                    } else {
                        // 🆕 **新しいエントリを追加**
                        $insertData[] = [
                            'id' => ++$lastId, // 新しい `id` はこの `order_code` とは独立しています**
                            'order_code' => $orderCode,
                            'job_type_code' => $newJobTypeCode,
                            'created_at' => now(),
                            'update_at' => now(),
                        ];
                    }
                }
            }

            // ❌ **削除: 新しく選択されたリストにない `job_type_code` を削除します**
            $jobTypeCodesToDelete = array_diff(array_keys($existingRecords->toArray()), $newJobTypeCodes);

            if (!empty($jobTypeCodesToDelete)) {
                DB::table('job_job_type')
                    ->where('order_code', $orderCode) // ✅ この`order_code`のみ
                    ->whereIn('job_type_code', $jobTypeCodesToDelete) // ✅ 必要な`job_type_code`のみを削除します
                    ->delete();
            }

            // 🟢 **追加: 新しい `job_type_code` を追加**
            if (!empty($insertData)) {
                DB::table('job_job_type')->insert($insertData);
            }
        });

        // 3. job_licenseデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
            // ✅ `order_code`が`company_code`に属しているかどうかを確認します
            $companyCode = DB::table('job_order')
                ->where('order_code', $orderCode)
                ->where('company_code', $companyUser->company_code) // **これは会社の所有物である必要があります**
                ->value('company_code');

            if (!$companyCode) {
                Log::warning("❌ この order_code はこの会社のものではありません。: {$orderCode}");
                return;
            }

            // ✅ この`order_code`に関連する既存の`job_license`データを取得します。
            $existingRecords = DB::table('job_license')
                ->where('order_code', $orderCode)
                ->select('id', 'group_code', 'category_code', 'code')
                ->get()
                ->mapWithKeys(function ($item) {
                    return ["{$item->group_code}-{$item->category_code}-{$item->code}" => $item->id];
                });

            // 🔵 **最大IDを定義します（定義しない場合は `0` になります）**
            $lastId = DB::table('job_license')
                ->where('order_code', $orderCode)
                ->max('id') ?? 0;

            $insertData = [];
            $newRecords = [];
            $usedIds = [];

            // ✅ **新しいエントリーを確認中です**
            foreach ($validatedData['qualifications'] as $qualification) {
                if (!empty($qualification['group_code']) && !empty($qualification['category_code']) && !empty($qualification['code'])) {
                    $key = "{$qualification['group_code']}-{$qualification['category_code']}-{$qualification['code']}";

                    // 🔄 **入手可能な場合は更新します**
                    if (isset($existingRecords[$key])) {
                        DB::table('job_license')
                            ->where('id', $existingRecords[$key]) // **これは更新されるIDです**
                            ->where('order_code', $orderCode) // **この `order_code` のみが更新されます**
                            ->update([
                                'update_at' => now(),
                            ]);
                        $usedIds[] = $existingRecords[$key]; // **使用されたID**
                    } else {
                        // 🆕 **新規追加**
                        $insertData[] = [
                            'id' => ++$lastId, // **新しいID**
                            'order_code' => $orderCode,
                            'group_code' => $qualification['group_code'],
                            'category_code' => $qualification['category_code'],
                            'code' => $qualification['code'],
                            'created_at' => now(),
                            'update_at' => now(),
                        ];
                    }
                }
            }

            // ❌ **削除: 未使用の `ids` を削除します**
            $idsToDelete = array_diff(array_values($existingRecords->toArray()), $usedIds);

            if (!empty($idsToDelete)) {
                DB::table('job_license')
                    ->where('order_code', $orderCode)
                    ->whereIn('id', $idsToDelete) // **これらの `ids` を削除します**
                    ->delete();
            }

            // 🟢 **追加: 新しい `job_license` を追加します**
            if (!empty($insertData)) {
                DB::table('job_license')->insert($insertData);
            }
        });

        // 4.job_skillデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
            // この order_code に関連付けられた company_code を取得します
            $companyCode = DB::table('job_order')
                ->where('order_code', $orderCode)
                ->where('company_code', $companyUser->company_code) // **この会社を確認中です**
                ->value('company_code'); // company_codeのみ取得します

            if (!$companyCode) {
                Log::warning("❌ この order_code はこの会社のものではありません。: {$orderCode}");
                return; // 企業が適応しなければ何も変わりません。
            }

            // ユーザーが新しい資格を入力すると、古いデータは削除されます。
            if (!empty($validatedData['skills'])) {
                DB::table('job_skill')
                    ->where('order_code', $orderCode)
                    ->delete(); // **この会社の `order_code` に基づいて注文のみを削除します**

                $lastId = 0;

                foreach ($validatedData['skills'] as $categoryCode => $skills) {
                    foreach ($skills as $skillCode) {
                        DB::table('job_skill')->insert([
                            'order_code' => $orderCode,
                            'id' => ++$lastId,
                            'category_code' => $categoryCode,
                            'code' => $skillCode,
                            'period' => 0,
                            'created_at' => now(),
                            'update_at' => now(),
                        ]);
                    }
                }
            }
        });

        // 5.job_supplement_infoデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
            // この order_code に関連付けられた company_code を取得します
            $companyCode = DB::table('job_order')
                ->where('order_code', $orderCode)
                ->where('company_code', $companyUser->company_code) // **関連会社を確認する**
                ->value('company_code');

            if (!$companyCode) {
                Log::warning("❌ この order_code はこの会社のものではありません。: {$orderCode}");
                return;
            }

            // 以前のデータを取得する
            $existingSupplement = DB::table('job_supplement_info')
                ->where('order_code', $orderCode)
                ->first();

            // 変化がなければ、私たちは何もしていないことになります。
            if (!$existingSupplement && empty(array_filter($validatedData))) {
                return;
            }

            $supplementData = [
                'order_code' => $orderCode,
                'company_code' => $companyUser->company_code,
                'process1' => $validatedData['process1'] ?? $existingSupplement->process1 ?? null,
                'process2' => $validatedData['process2'] ?? $existingSupplement->process2 ?? null,
                'process3' => $validatedData['process3'] ?? $existingSupplement->process3 ?? null,
                'process4' => $validatedData['process4'] ?? $existingSupplement->process4 ?? null,
                'company_speciality' => $validatedData['company_speciality'] ?? $existingSupplement->company_speciality ?? null,
                'catch_copy' => $validatedData['catch_copy'] ?? $existingSupplement->catch_copy ?? null,
                'pr_title1' => $validatedData['pr_title1'] ?? $existingSupplement->pr_title1 ?? null,
                'pr_title2' => $validatedData['pr_title2'] ?? $existingSupplement->pr_title2 ?? null,
                'pr_title3' => $validatedData['pr_title3'] ?? $existingSupplement->pr_title3 ?? null,
                'pr_contents1' => $validatedData['pr_contents1'] ?? $existingSupplement->pr_contents1 ?? null,
                'pr_contents2' => $validatedData['pr_contents2'] ?? $existingSupplement->pr_contents2 ?? null,
                'pr_contents3' => $validatedData['pr_contents3'] ?? $existingSupplement->pr_contents3 ?? null,
                'biz_name1' => $validatedData['biz_name1'] ?? $existingSupplement->biz_name1 ?? null,
                'biz_name2' => $validatedData['biz_name2'] ?? $existingSupplement->biz_name2 ?? null,
                'biz_name3' => $validatedData['biz_name3'] ?? $existingSupplement->biz_name3 ?? null,
                'biz_name4' => $validatedData['biz_name4'] ?? $existingSupplement->biz_name4 ?? null,
                'biz_percentage1' => $validatedData['biz_percentage1'] ?? $existingSupplement->biz_percentage1 ?? null,
                'biz_percentage2' => $validatedData['biz_percentage2'] ?? $existingSupplement->biz_percentage2 ?? null,
                'biz_percentage3' => $validatedData['biz_percentage3'] ?? $existingSupplement->biz_percentage3 ?? null,
                'biz_percentage4' => $validatedData['biz_percentage4'] ?? $existingSupplement->biz_percentage4 ?? null,
                'update_at' => now(),
            ];

            DB::table('job_supplement_info')->updateOrInsert(
                ['order_code' => $orderCode],
                $supplementData
            );
        });

        // 6.job_noteデータを保存する
        DB::transaction(function () use ($request, $orderCode, $companyUser) {
            // この order_code に関連付けられた company_code を取得します
            $companyCode = DB::table('job_order')
                ->where('order_code', $orderCode)
                ->where('company_code', $companyUser->company_code) // **関連会社を確認する**
                ->value('company_code');

            if (!$companyCode) {
                Log::warning("❌ この order_code はこの会社のものではありません。: {$orderCode}");
                return;
            }

            // 古いデータを取得する
            $existingNote = DB::table('job_note')
                ->where('order_code', $orderCode)
                ->where('category_code', 'Note')
                ->where('code', 'BestMatch')
                ->first();

            // 新しい値がある場合はそれを保存します
            $noteData = [
                'order_code' => $orderCode,
                'category_code' => 'Note',
                'code' => 'BestMatch',
                'note' => $request->input('BestMatch') ?? $existingNote->note ?? null,
                'update_at' => now(),
            ];

            // 古いレコードがない場合は、`created_at`を追加します
            if (!$existingNote) {
                $noteData['created_at'] = now();
            }

            DB::table('job_note')->updateOrInsert(
                ['order_code' => $orderCode, 'category_code' => 'Note', 'code' => 'BestMatch'],
                $noteData
            );
        });

        // 7.job_scheduled_to_intraduntionデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
            // この order_code に関連付けられた company_code を取得します
            $companyCode = DB::table('job_order')
                ->where('order_code', $orderCode)
                ->where('company_code', $companyUser->company_code) // **関連会社を確認する**
                ->value('company_code');

            if (!$companyCode) {
                Log::warning("❌ この order_code はこの会社のものではありません。: {$orderCode}");
                return;
            }

            // 古いデータを取得する
            $existingScheduled = DB::table('job_scheduled_to_intraduntion')
                ->where('order_code', $orderCode)
                ->first();

            $scheduledData = [
                'employment_start_day' => isset($validatedData['employment_start_day']) ?
                    Carbon::createFromFormat('Ymd', $validatedData['employment_start_day'])->format('Y-m-d H:i:s')
                    : ($existingScheduled->employment_start_day ?? null),
                'work_start_day' => isset($validatedData['work_start_day']) ?
                    Carbon::createFromFormat('Ymd', $validatedData['work_start_day'])->format('Y-m-d H:i:s')
                    : ($existingScheduled->work_start_day ?? null),
                'work_end_day' => isset($validatedData['work_end_day']) ?
                    Carbon::createFromFormat('Ymd', $validatedData['work_end_day'])->format('Y-m-d H:i:s')
                    : ($existingScheduled->work_end_day ?? null),
                'work_period' => $validatedData['work_period'] ?? $existingScheduled->work_period ?? 0,
                'work_update_flag' => $validatedData['work_update_flag'] ?? $existingScheduled->work_update_flag ?? 0,
                'new_graduate_flag' => $validatedData['new_graduate_flag'] ?? $existingScheduled->new_graduate_flag ?? 0,
                'update_at' => now(),
            ];

            DB::table('job_scheduled_to_intraduntion')->updateOrInsert(
                ['order_code' => $orderCode],
                $scheduledData
            );
        });

        // 8.job_working_conditionデータを保存する
        // 8.work_start_time & work_end_time - rest_start_time & rest_end_time
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
            // この order_code に関連付けられた company_code を取得します
            $companyCode = DB::table('job_order')
                ->where('order_code', $orderCode)
                ->where('company_code', $companyUser->company_code) //  **関連会社を確認する**
                ->value('company_code');

            if (!$companyCode) {
                Log::warning("❌ この order_code はこの会社のものではありません。: {$orderCode}");
                return;
            }

            // 古いデータを取得する
            $existingCondition = DB::table('job_working_condition')
                ->where('order_code', $orderCode)
                ->first();

            $workingConditionData = [
                'work_start_time' => $validatedData['work_start_time'] ?? $existingCondition->work_start_time ?? null,
                'Work_end_time' => $validatedData['Work_end_time'] ?? $existingCondition->Work_end_time ?? null,
                'rest_start_time' => $validatedData['rest_start_time'] ?? $existingCondition->rest_start_time ?? null,
                'rest_end_time' => $validatedData['rest_end_time'] ?? $existingCondition->rest_end_time ?? null,
                'update_at' => now(),
            ];

            DB::table('job_working_condition')->updateOrInsert(
                ['order_code' => $orderCode],
                $workingConditionData
            );
        });

        // 9. job_working_placeデータを保存する
        DB::transaction(function () use ($validatedData, $orderCode, $companyUser) {
            // ✅ この order_code に関連付けられた company_code を取得します
            $companyCode = DB::table('job_order')
                ->where('order_code', $orderCode)
                ->where('company_code', $companyUser->company_code)
                ->value('company_code');

            if (!$companyCode) {
                Log::warning("❌ この order_code はこの会社のものではありません。: {$orderCode}");
                return;
            }

            // ✅ 既存の `working_place_seq` を取得する
            $existingRecords = DB::table('job_working_place')
                ->where('order_code', $orderCode)
                ->select('working_place_seq', 'prefecture_code', 'city', 'town', 'address')
                ->get()
                ->keyBy('working_place_seq');

            // 🔵 **最大の `working_place_seq` を取得します (ない場合は `0`)**
            $lastSeq = DB::table('job_working_place')
                ->where('order_code', $orderCode)
                ->max('working_place_seq') ?? 0;

            $newRecords = [];
            $usedSeqs = [];

            foreach ($validatedData['prefecture_code'] as $key => $prefectureCode) {
                $city = $validatedData['city'][$key] ?? null;
                $town = $validatedData['town'][$key] ?? null;
                $address = $validatedData['address'][$key] ?? null;

                // 🔄 **`working_place_seq` が存在する場合 – 更新します**
                if (isset($existingRecords[$key + 1])) {
                    DB::table('job_working_place')
                        ->where('working_place_seq', $key + 1)
                        ->where('order_code', $orderCode)
                        ->update([
                            'prefecture_code' => $prefectureCode,
                            'city' => $city,
                            'town' => $town,
                            'address' => $address,
                            // 'update_at' => now(),
                        ]);
                    $usedSeqs[] = $key + 1;
                } else {
                    // 🆕 **新しい `working_place_seq` を作成します**
                    $newRecords[] = [
                        'order_code' => $orderCode,
                        'working_place_seq' => ++$lastSeq,
                        'area' => '日本',
                        'prefecture_code' => $prefectureCode,
                        'city' => $city,
                        'town' => $town,
                        'address' => $address,
                        // 'created_at' => now(),
                        // 'update_at' => now(),
                    ];
                }
            }

            // ❌ **古くて使われていない「working_place_seq」を削除しています**
            $seqsToDelete = array_diff(array_keys($existingRecords->toArray()), $usedSeqs);

            if (!empty($seqsToDelete)) {
                DB::table('job_working_place')
                    ->where('order_code', $orderCode)
                    ->whereIn('working_place_seq', $seqsToDelete)
                    ->delete();
            }

            // 🟢 **新しい `working_place_seq` の追加**
            if (!empty($newRecords)) {
                DB::table('job_working_place')->insert($newRecords);
            }
        });


        Log::info('検証のために受信したデータ: ', $validatedData);
        // dd($request->all());

        // 求人情報の更新をメールで通知する
        // `order_type` を取得する
        $orderType = $validatedData['order_type'];
        $this->sendAgentNotification($orderCode, $orderType, 'update');

        return redirect()->route('jobs.job_list', ['order_code' => $orderCode])
            ->with('success', '更新が成功しました。');
    }
}
