<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\PersonHopeWorkingCondition;


class MatchingsController extends Controller
{
    /**
     * マッチング登録用の作成フォームを表示します。
     */
    public function create(Request $request)
    {
        $staffCode = Auth::id();
        // 全国 (全地域共通)
        $allOption = [
            'code' => '000',
            'detail' => '全国',
        ];
        $checkboxOptions = [
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
            'telework_flag' => 'テレワーク可',
            'meals_assistance_flag' => '食事補助',
            'training_cost_flag' => '研修費用支給',
            'entrepreneur_cost_flag' => '起業補助',
            'money_flag' => '金銭補助',
            'land_shop_flag' => '店舗提供',
            'find_job_festive_flag' => '就職祝金',
            'appointment_flag' => '正社員登用'
        ];


        // 業種 (ビッグクラスデータの取得)
        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();
        // グループのデータを取得する（ライセンス用）
        $groups = DB::table('master_license')
            ->select('group_code', 'group_name')
            ->distinct()
            ->get();

        // 地域と都道府県を取得する
        $regions = DB::table('master_code')
            ->where('category_code', 'Region')
            ->get();

        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->get();

        // 地域に属する都道府県のグループ化 (Regionlarga tegishli prefekturalarni guruhlash)
        $regionGroups = $regions->map(function ($region) use ($prefectures) {
            return [
                'detail' => $region->detail,
                'prefectures' => $prefectures->filter(function ($prefecture) use ($region) {
                    return $prefecture->region_code === $region->code;
                }),
            ];
        });

        // 個別都道府県 (各都道府県)
        $individualPrefectures = $prefectures->map(function ($prefecture) {
            return [
                'code' => $prefecture->code, // 都道府県コード
                'detail' => $prefecture->detail, // 都道府県の名前
            ];
        })->toArray(); // 配列に変換する


        // マッチング登録フォームを表示 (すべてのデータを送信してviewに表示する)
        return view('matchings.create', compact(
            'bigClasses', // ジョブクラス
            'allOption', // すべて (全国)
            'groups',
            'regionGroups', // 地域
            'individualPrefectures', // 各都道府県
            'checkboxOptions',
        ));
    }

    public function getJobTypes(Request $request)
    {
        $bigClassCode = $request->input('big_class_code');

        Log::info("Big Class Code received: {$bigClassCode}");

        if (!$bigClassCode) {
            return response()->json(['error' => 'Invalid Big Class Code'], 400);
        }

        $jobTypes = DB::table('master_job_type')
            ->where('big_class_code', $bigClassCode)
            ->select('middle_class_code', 'middle_clas_name')
            ->get();

        Log::info('Job Types fetched:', $jobTypes->toArray());

        if ($jobTypes->isEmpty()) {
            return response()->json([], 204);
        }

        return response()->json($jobTypes);
    }

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
     * 一致する設定を DB に保存します。
     */
    public function store(Request $request)
    {
        $person = Auth::user();
        Log::info("Store method started. Staff Code: {$person->staff_code}");

        // データの検証
        $request->validate([
            'gender' => 'required|in:1,2',
            'birthday' => 'nullable|digits:8',
            'post_u' => 'nullable|digits:3',
            'post_l' => 'nullable|digits:4',
            'city' => 'nullable|string',
            'city_f' => 'nullable|string',
            'town' => 'nullable|string',
            'town_f' => 'nullable|string',
            'address' => 'nullable|string',
            'address_f' => 'nullable|string',
            'phone_number' => 'required|string',
            'big_class_code' => 'required|exists:master_job_type,big_class_code',
            'job_category' => 'required|exists:master_job_type,middle_class_code',
            'group_code' => 'nullable|exists:master_license,group_code',
            'category_code' => 'nullable|exists:master_license,category_code',
            'license_code' => 'nullable|exists:master_license,code',
            'prefecture_code' => 'required|array', // 配列である必要があります
            'prefecture_code.*' => 'string', // 各要素は文字列である必要があります
            'desired_salary_type' => 'required|in:年収,時給',
            'desired_salary_annual' => 'nullable|numeric|required_if:desired_salary_type,年収',
            'desired_salary_hourly' => 'nullable|numeric|required_if:desired_salary_type,時給',
        ]);
        $staffCode = Auth::id();
        Log::info("Validation passed for Staff Code: {$person->staff_code}");

        // ✅ ユーザーが誕生日を入力していない場合は、null に設定されます。
        $birthdayFormatted = $request->filled('birthday')
            ? \Carbon\Carbon::createFromFormat(
                'Ymd',
                $request->birthday
            )->format('Y-m-d 00:00:00')
            : null;

        // ✅ ユーザーが postal_code を入力しなかった場合は、それを null に設定します。
        $postU = $request->filled('post_u') ? $request->post_u : null;
        $postL = $request->filled('post_l') ? $request->post_l : null;

        // master_person テーブルを更新する
        DB::table('master_person')->updateOrInsert(
            ['staff_code' => $person->staff_code],
            [
                'staff_code' => $person->staff_code,
                'sex' => $request->gender,
                'birthday' => $birthdayFormatted,
                'post_u' => $postU,
                'post_l' => $postL,
                'city' => $request->city,
                'city_f' => $request->city_f,
                'town' => $request->town,
                'town_f' => $request->town_f,
                'address' => $request->address,
                'address_f' => $request->address_f,
                'portable_telephone_number' => $request->phone_number,
            ]
        );

        Log::info("Updated master_person table for Staff Code: {$person->staff_code}");

        if ($request->group_code && $request->category_code && $request->license_code) {
            // ライセンスコメントの「master_license」テーブルから列「name」を取得します。
            $licenseName = DB::table('master_license')
                ->where('group_code', $request->group_code)
                ->where('category_code', $request->category_code)
                ->where('code', $request->license_code)
                ->value('name');

            if (!$licenseName) {
                return back()->withErrors(['license_code' => '選択したライセンスは使用できません.']);
            }

            // 「person_license」テーブルの「id」を計算する
            $maxId = DB::table('person_license')
                ->where('staff_code', $staffCode)
                ->max('id');

            $newId = $maxId ? $maxId + 1 : 1; // 存在する場合は +1、存在しない場合は 1 から開始します

            // ライセンスを person_license テーブルに保存します
            DB::table('person_license')->updateOrInsert([
                'staff_code' => $staffCode,
                'id' => $newId,
                'group_code' => $request->group_code,
                'category_code' => $request->category_code,
                'code' => $request->license_code,
                'remark' => $licenseName,
                'get_day' => now(),
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info("Inserted license data into person_license for Staff Code: {$staffCode}, ID: {$newId}");
        } else {
            Log::info("License data not provided. Skipping person_license insertion for Staff Code: {$staffCode}");
        }

        // 雇用条件の策定
        $desiredSalaryType = $request->input('desired_salary_type');
        $desiredSalaryAnnual = $request->input('desired_salary_annual')
            ? $request->input('desired_salary_annual') * 10000
            : null;
        $desiredSalaryHourly = $request->input('desired_salary_hourly') ?? null;

        Log::info("Processed salary conditions for Staff Code: {$person->staff_code}");

        // ✅ 新規ユーザーの場合、**すべての列は 0 またはデフォルトになります**
        PersonHopeWorkingCondition::updateSelectiveOrCreate($staffCode, [
            'hourly_income_min' => $desiredSalaryType === '時給' ? $desiredSalaryHourly : 0,
            'yearly_income_min' => $desiredSalaryType === '年収' ? $desiredSalaryAnnual : 0,
        ]);


        Log::info("Updated person_hope_working_condition table for Staff Code: {$person->staff_code}");

        // 仕事の仕事
        $prefectureCodes = is_array($request->prefecture_code)
            ? $request->prefecture_code
            : [$request->prefecture_code];

        DB::table('person_hope_working_place')->where('staff_code', $person->staff_code)->delete();
        Log::info("Cleared previous entries in person_hope_working_place for Staff Code: {$person->staff_code}");

        foreach ($prefectureCodes as $prefectureCode) {
            $maxId = DB::table('person_hope_working_place')
                ->where('staff_code', $person->staff_code)
                ->max('id');
            $newId = $maxId ? $maxId + 1 : 1;

            DB::table('person_hope_working_place')->updateOrInsert([
                'staff_code' => $person->staff_code,
                'id' => $newId,
                'prefecture_code' => $prefectureCode,
                'city' => $request->city ?? null,
                'area' => $request->area ?? '日本',
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info("Inserted entry in person_hope_working_place: Staff Code: {$person->staff_code}, ID: {$newId}, Prefecture Code: {$prefectureCode}");
        }

        $bigClassName = DB::table('master_job_type')
            ->where('big_class_code', $request->big_class_code)
            ->value('big_class_name');

        if (!$bigClassName) {
            Log::error("Big class name not found for code: {$request->big_class_code}");
            return back()->withErrors(['big_class_code' => '選択した業種は存在しません。']);
        }

        $middleClassName = DB::table('master_job_type')
            ->where('middle_class_code', $request->job_category)
            ->where('big_class_code', $request->big_class_code)
            ->value('middle_clas_name');

        if (!$middleClassName) {
            Log::error("Middle class name not found or doesn't match big_class_code: {$request->job_category}");
            return back()->withErrors(['job_category' => '選択した職種タイプは存在しません。']);
        }

        $bigClassCode = $request->big_class_code;
        $middleClassCode = $request->job_category;

        $newJobTypeCode = $bigClassCode . $middleClassCode . "000";

        $jobTypeDetail = $middleClassName;

	$existId = DB::table('person_hope_job_type')
	    ->where('staff_code', $person->staff_code)
	    ->where('job_type_code', $newJobTypeCode)
	   ->first('id');

        $maxId = DB::table('person_hope_job_type')
            ->where('staff_code', $person->staff_code)
            ->max('id');

        //$newId = $maxId ? $maxId + 1 : 1;
	if($existId) {
	    $newId = $existId;
	} else {
	    $newId = $maxId ? $maxId + 1 : 1;
	} 


        DB::table('person_hope_job_type')->updateOrInsert([
            'staff_code' => $person->staff_code,
            'id' => $newId,
            'job_type_code' => $newJobTypeCode,
            'job_type_detail' => $jobTypeDetail,
            'created_at' => now(),
            'update_at' => now(),
        ]);
	//dd("Inserted into person_hope_job_type: Staff Code: {$person->staff_code}, ID: {$newId}, Job Type Code: {        $newJobTypeCode}, Job Type Detail: {$jobTypeDetail}");

        Log::info("Inserted into person_hope_job_type: Staff Code: {$person->staff_code}, ID: {$newId}, Job Type Code: {$newJobTypeCode}, Job Type Detail: {$jobTypeDetail}");


        // レコードが正常に記録されたことを示します
        Log::info("スタッフコードの成功メッセージを含むマイページへのリダイレクト: {$person->staff_code}");
        return redirect()->route('mypage')->with('message', '登録しました。');
    }


    public function showMatchingResults(Request $request)
    {
        $staffCode = Auth::id();
        Log::info("🔍 Staff Code:", ['staff_code' => $staffCode]);

        // ユーザーデータの可用性を確認する
        $hasWorkingCondition = DB::table('person_hope_working_condition')
            ->where('staff_code', $staffCode)
            ->select('yearly_income_min', 'hourly_income_min')
            ->first();
        Log::info("📌 User's Working Condition:", (array) $hasWorkingCondition);

        // 選択した給与タイプが利用できない場合は、ユーザーを差し戻します
        if (!$hasWorkingCondition) {
            Log::error("❌ User has no working condition data.");
            return redirect()->route('matchings.create')->withErrors(['msg' => '希望条件を登録してください。']);
        }

        $desiredSalaryType = null;
        if (!is_null($hasWorkingCondition->yearly_income_min)) {
            $desiredSalaryType = '年収';
        } elseif (!is_null($hasWorkingCondition->hourly_income_min)) {
            $desiredSalaryType = '時給';
        } else {
            Log::error("❌ No valid salary type found!");
            return redirect()->route('matchings.create')->withErrors(['msg' => '給与タイプを選択してください。']);
        }
        Log::info("✅ Selected Salary Type:", ['desiredSalaryType' => $desiredSalaryType]);

        $personHopeWorkingCondition = DB::table('person_hope_working_condition')
            ->where('staff_code', $staffCode)
            ->select('hourly_income_min', 'yearly_income_min')
            ->first();

        Log::info("📌 User's Hope Working Condition:", (array) $personHopeWorkingCondition);

        if (is_null($personHopeWorkingCondition)) {
            Log::error("❌ person_hope_working_condition NULL! Staff Code: {$staffCode}");
        } else {
            Log::info("📌 User's Hope Working Condition:", (array) $personHopeWorkingCondition);
        }

        // ユーザーの業務内容の要望を把握する
        $personHopeJobTypesCode = DB::table('person_hope_job_type')
            ->where('staff_code', $staffCode)
            ->pluck('job_type_code')
            ->toArray();
        Log::info("📌 User's Preferred Job Types:", $personHopeJobTypesCode);

        $personHopeWorkingPlaces = DB::table('person_hope_working_place')
            ->where('staff_code', $staffCode)
            ->pluck('prefecture_code')
            ->toArray();
        Log::info("📌 User's Preferred Working Places:", $personHopeWorkingPlaces);

        $personLicense = DB::table('person_license')
            ->where('staff_code', $staffCode)
            ->select('group_code', 'category_code', 'code')
            ->get()
            ->toArray();
        Log::info("📌 User's Licenses:", $personLicense);
        $groupCodes = array_column($personLicense, 'group_code');
        $categoryCodes = array_column($personLicense, 'category_code');
        $codes = array_column($personLicense, 'code');

        // 🎯 炭素による年齢計算
        $personBirthDay = DB::table('master_person')
            ->where('staff_code', $staffCode)
            ->select('birthday')
            ->first();
        Log::info("📌 User's Birthday:", (array) $personBirthDay);

        $personAge = $personBirthDay ? Carbon::parse($personBirthDay->birthday)->age : null;
        Log::info("📌 Calculated Age:", ['age' => $personAge]);

        $selectedFlags = $request->input('supplement_flags', []);
        $checkboxOptions = $this->checkboxOptions();
        Log::info("📌 Selected Supplement Flags:", $selectedFlags);

        // マッチングする仕事を獲得する
        DB::enableQueryLog();
        Log::info("🔍 Starting Query Execution...");

        $matchingResults = DB::table('job_order')
            ->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
            ->join('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
            ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
            ->join('master_code', function ($join) {
                $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                    ->where('master_code.category_code', '=', 'Prefecture');
            })
            ->leftJoin('job_license', 'job_order.order_code', '=', 'job_license.order_code')
            ->leftJoin('job_skill', 'job_order.order_code', '=', 'job_skill.order_code')
            ->leftJoin('log_access_history_order', 'job_order.order_code', '=', 'log_access_history_order.order_code')
            ->join('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
            ->select(
                'job_order.order_code as id',
                'job_order.job_type_detail',
                'job_order.yearly_income_min',
                'job_order.yearly_income_max',
                'job_order.hourly_income_min',
                'job_order.hourly_income_max',
                'master_company.company_name_k',
                'master_code.detail as prefecture_name',
                'job_working_place.city',
                'job_working_place.town',
                'job_job_type.job_type_code',
                'job_supplement_info.pr_title1',
                'job_supplement_info.pr_contents1',
                DB::raw('COALESCE(SUM(log_access_history_order.browse_cnt), 0) as browse_cnt'), // `browse_cnt`が存在しない場合は0が返されます。

            )
            ->addSelect(array_keys($checkboxOptions))
            ->when(!empty($selectedFlags), function ($query) use ($selectedFlags) {
                foreach ($selectedFlags as $flag) {
                    $query->where("job_supplement_info.$flag", '=', '1');
                }
            })
            ->when(!empty($personHopeJobTypesCode), function ($query) use ($personHopeJobTypesCode) {
                return $query->whereIn('job_job_type.job_type_code', $personHopeJobTypesCode);
            })
            ->when(!empty($personHopeWorkingPlaces), function ($query) use ($personHopeWorkingPlaces) {
                return $query->whereIn('job_working_place.prefecture_code', $personHopeWorkingPlaces);
            })

            ->when($desiredSalaryType === '時給' && !is_null($personHopeWorkingCondition) && $personHopeWorkingCondition->hourly_income_min > 0, function ($query) use ($personHopeWorkingCondition) {
                Log::info("📌 Filtering jobs based on hourly salary:", ['hourly_income_min' => $personHopeWorkingCondition->hourly_income_min]);
                return $query->where('job_order.hourly_income_min', '>=', $personHopeWorkingCondition->hourly_income_min);
            })
            ->when($desiredSalaryType === '年収' && !is_null($personHopeWorkingCondition) && $personHopeWorkingCondition->yearly_income_min > 0, function ($query) use ($personHopeWorkingCondition) {
                Log::info("📌 Filtering jobs based on yearly salary:", ['yearly_income_min' => $personHopeWorkingCondition->yearly_income_min]);
                return $query->where('job_order.yearly_income_min', '>=', $personHopeWorkingCondition->yearly_income_min);
            })
            // ->when(!empty($groupCodes) && !empty($categoryCodes) && !empty($codes), function ($query) use ($groupCodes, $categoryCodes, $codes) {
            //     return $query->whereIn('job_license.group_code', $groupCodes)
            //                  ->whereIn('job_license.category_code', $categoryCodes)
            //                  ->whereIn('job_license.code', $codes);
            // })
            


            ->where(function ($query) use ($personAge) {
                $query->where('job_order.age_max', '>=', $personAge)
                    ->orWhere('job_order.age_max', '=', '0');
            })
            ->where('job_order.public_flag', '=', 1)
            ->where('job_order.order_progress_type', '=', 1)
            ->where('job_order.public_limit_day', '>=', now())
            // ->where(function ($query) {
            //     $query->where(function ($subQuery) {
            //         $subQuery->where('master_company.keiyaku_ymd', '<>', ''); // keiyaku_ymdは空でないかどうかをチェックします
            //         // ->orWhereNotNull('master_company.keiyaku_ymd'); // 今のところこの行にコメントを付けることができます, 今のところこの行にコメントを付けることができます
            //     })
            //         ->orWhere(function ($subQuery) {
            //             $subQuery->where('master_company.intbase_contract_day', '>=', '1900-00-00 00:00:00'); // intbase_contract_day が false でないかどうかを確認します
            //         });
            // })

            // ->where('job_order.recruitment_limit_day', '>=', now())

            ->groupBy(
                'job_order.order_code',
                'job_order.job_type_detail',
                'job_order.yearly_income_min',
                'job_order.yearly_income_max',
                'job_order.hourly_income_min',
                'job_order.hourly_income_max',
                'master_company.company_name_k',
                'master_code.detail',
                'job_working_place.city',
                'job_working_place.town',
                'job_job_type.job_type_code',
                'job_supplement_info.pr_title1',
                'job_supplement_info.pr_contents1',
            )
            ->orderBy('job_order.update_at', 'desc')
            ->distinct()
            ->paginate(6)
            ->appends($request->all());

        Log::info('Query Log:', DB::getQueryLog());

        // foreach ($matchingResults as $result) {
        //     $result->yearly_income_display = "{$result->yearly_income_min}円〜" . ($result->yearly_income_max > 0 ? "{$result->yearly_income_max}円" : '');
        //     $result->hourly_income_display = "{$result->hourly_income_min}円〜" . ($result->hourly_income_max > 0 ? "{$result->hourly_income_max}円" : '');
        // }
        foreach ($matchingResults as $result) {
            $result->yearly_income_display = $result->yearly_income_min . '円' .
                (isset($result->yearly_income_max) && $result->yearly_income_max > 0
                    ? '〜' . $result->yearly_income_max . '円'
                    : '〜');
            $result->hourly_income_display = $result->hourly_income_min . '円' .
                (isset($result->hourly_income_max) && $result->hourly_income_max > 0
                    ? '〜' . $result->hourly_income_max . '円'
                    : '〜');
        }
        // チェックボックスのオプション
        $checkboxOptions = $this->checkboxOptions();

        foreach ($matchingResults as $job) {
            $job->selectedFlagsArray = [];
            foreach ($checkboxOptions as $key => $label) {
                if (!empty($job->$key) && $job->$key == 1) {
                    $job->selectedFlagsArray[] = $key;
                }
            }
        }

	//dd($matchingResults);	


        Log::info('📌 Salary Formatting Completed');
        return view('matchings.results', compact('matchingResults', 'selectedFlags', 'checkboxOptions', 'desiredSalaryType'));
    }


    private static function checkboxOptions()
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
            'mammy_flag' => '主婦(夫)',
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
            'appointment_flag' => '正社員登録',
        ];
    }

    public static function detail($id)
    {
        // クエリを開始する前に
        DB::enableQueryLog();

        $staffCode = Auth::id();
        // 求人情報を取得する
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
                'job_order.order_code as id',
                'job_order.order_code',
                'job_order.company_code',
                'job_order.order_type',
                'job_order.job_type_detail',
                'job_order.business_detail',
                'job_order.yearly_income_min',
                'job_order.yearly_income_max',
                'job_order.hourly_income_min',
                'job_order.hourly_income_max',
                'job_order.income_remark',
                'job_order.work_time_remark',
                'job_order.holiday_remark',
                'job_supplement_info.pr_title1',
                'job_supplement_info.pr_contents1',
                'job_supplement_info.pr_title2',
                'job_supplement_info.pr_contents2',
                'job_supplement_info.pr_title3',
                'job_supplement_info.pr_contents3',
                'master_company.company_name_k as company_name',
                DB::raw('GROUP_CONCAT(DISTINCT master_code.detail SEPARATOR ", ") as all_prefectures'),
                DB::raw('GROUP_CONCAT(DISTINCT skill_master.detail SEPARATOR ", ") as skill_detail'),
                'job_working_place.city',
                'job_working_place.town',
                'job_working_place.address',
                'job_job_type.job_type_code',
                'job_supplement_info.inexperienced_person_flag',
                'job_supplement_info.balancing_work_flag',
                'job_supplement_info.ui_turn_flag',
                'job_supplement_info.many_holiday_flag',
                'job_supplement_info.flex_time_flag',
                'job_supplement_info.near_station_flag',
                'job_supplement_info.no_smoking_flag',
                'job_supplement_info.newly_built_flag',
                'job_supplement_info.landmark_flag',
                'job_supplement_info.renovation_flag',
                'job_supplement_info.designers_flag',
                'job_supplement_info.company_cafeteria_flag',
                'job_supplement_info.short_overtime_flag',
                'job_supplement_info.maternity_flag',
                'job_supplement_info.dress_free_flag',
                'job_supplement_info.mammy_flag',
                'job_supplement_info.fixed_time_flag',
                'job_supplement_info.short_time_flag',
                'job_supplement_info.handicapped_flag',
                'job_supplement_info.rent_all_flag',
                'job_supplement_info.rent_part_flag',
                'job_supplement_info.meals_flag',
                'job_supplement_info.meals_assistance_flag',
                'job_supplement_info.training_cost_flag',
                'job_supplement_info.entrepreneur_cost_flag',
                'job_supplement_info.money_flag',
                'job_supplement_info.telework_flag',
                'job_supplement_info.land_shop_flag',
                'job_supplement_info.find_job_festive_flag',
                'job_supplement_info.appointment_flag'
            )
            ->where('job_order.order_code', $id)
            ->groupBy(
                'job_order.order_code',
                'job_order.company_code',
                'job_order.job_type_detail',
                'job_order.business_detail',
                'job_order.yearly_income_min',
                'job_order.yearly_income_max',
                'job_order.hourly_income_min',
                'job_order.hourly_income_max',
                'job_supplement_info.pr_title1',
                'job_supplement_info.pr_contents1',
                'job_supplement_info.pr_title2',
                'job_supplement_info.pr_contents2',
                'job_supplement_info.pr_title3',
                'job_supplement_info.pr_contents3',
                'master_company.company_name_k',
                'job_working_place.city',
                'job_working_place.town',
                'job_working_place.address',
                'job_job_type.job_type_code',
                'job_order.income_remark',
                'job_order.work_time_remark',
                'job_order.holiday_remark',
            )
            ->first();

        //if ($job ) {
        //    //$matchCount = $matchingJobs->total(); //count($matchingJobs);
        //    DB::table('log_person_signin')
        //        ->updateOrInsert(
        //            ['staff_code' => $staffCode],
        //            fn($exists) => $exists ? [
        //                'staff_code' => $staffCode,
        //                'detail_count' => 1,
        //                'update_at' => now(),
        //            ] : [
        //                'staff_code' => $staffCode,
        //                'detail_count' =>  DB::raw('detail_count+1'),
        //                'created_at' => now(),
        //                'update_at' => now(),
        //            ],
        //        );
        //}


        // 仕事が見つからない場合
        if (!$job) {
            return redirect()->route('matchings.results')->withErrors(['msg' => 'ジョブの詳細は見つかりませんでした。']);
        }

        // 給与の種類を決める (Desired Salary Type aniqlash)
        $desiredSalaryType = null;
        if (!is_null($job->yearly_income_min) && $job->yearly_income_min > 0) {
            $desiredSalaryType = '年収';
        } elseif (!is_null($job->hourly_income_min) && $job->hourly_income_min > 0) {
            $desiredSalaryType = '時給';
        }

        // チェックボックスのオプション
        $checkboxOptions = self::checkboxOptions();
        $selectedFlagsArray = [];
        foreach ($checkboxOptions as $key => $label) {
            if (!empty($job->$key) && $job->$key == 1) {
                $selectedFlagsArray[] = $key;
            }
        }

        // 都道府県を配列に入れる
        $prefecturesArray = DB::table('job_working_place')
            ->join('master_code', function ($join) {
                $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                    ->where('master_code.category_code', '=', 'Prefecture');
            })
            ->where('job_working_place.order_code', $id)
            ->distinct()
            ->pluck('master_code.detail')
            ->toArray();
        $locations = DB::table('job_working_place')
            ->join('master_code', function ($join) {
                $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                    ->where('master_code.category_code', '=', 'Prefecture');
            })
            ->where('job_working_place.order_code', $id)
            ->select(
                'master_code.detail as prefecture',
                'job_working_place.city',
                'job_working_place.town',
                'job_working_place.address'
            )
            ->distinct()
            ->get();
        //Offerされているか
        $offer = DB::table('person_offer')
            ->select('order_code', 'offer_flag')
            ->where('order_code', $job->id)
            //->orwhere('staff_code' ,$staffCode)
            ->first();
        $isOffer = $offer ? true : false;
        if ($isOffer) {
            $offerFlag = $offer->offer_flag;
        } else {
            $offerFlag = '0';
        }
        //working_time
        $workingTime = DB::table('job_working_condition')
            ->where('job_working_condition.order_code', $id)
            ->first();

           DB::table('log_person_signin')
                ->updateOrInsert(
                    ['staff_code' => $staffCode],
                    fn($exists) => $exists ? [
                        'staff_code' => $staffCode,
                        'detail_count' => DB::raw('detail_count+1'),
                        'update_at' => now(),
                    ] : [
                        'staff_code' => $staffCode,
                        'detail_count' =>  DB::raw('detail_count+1'),
                        'created_at' => now(),
                        'update_at' => now(),
                    ],
                );


        // テーブル内の既存のレコードを検索し、自動更新または挿入
        DB::transaction(function () use ($id, $staffCode) {
            DB::statement('INSERT INTO log_access_history_order (order_code, staff_code, created_at, update_at, browse_cnt)
            VALUES (?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE
                browse_cnt = browse_cnt + 1,
                update_at = ?', [
                $id,
                $staffCode,
                now(),
                now(),
                now(),
            ]);

            // log_access_history_staff テーブルにも同様に挿入または更新
            DB::statement('INSERT INTO log_access_history_staff (order_code, staff_code, created_at, update_at)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            update_at = ?', [
                $id,
                $staffCode,
                now(),
                now(),
                now(),
            ]);
        });

        // 最新の合計閲覧数を取得
        $viewCount = DB::table('log_access_history_order')
            ->where('order_code', $id)
            ->value('browse_cnt');


        // ログに情報を記録
        Log::info('ID:', ['id' => $id]);
        Log::info('View Count:', ['view_count' => $viewCount]);
        Log::info('Query Log:', DB::getQueryLog());
        Log::info('Prefectures:', $prefecturesArray);

        return view('matchings.detail', compact('job', 'checkboxOptions', 'selectedFlagsArray', 'desiredSalaryType', 'prefecturesArray', 'isOffer', 'offerFlag', 'workingTime', 'locations'));
    }

    public function edit()
    {
        $staffCode = Auth::id();
        $person = DB::table('master_person')->where('staff_code', $staffCode)->first();

        // 📌 ユーザーの給与情報を取得する
        $personHopeWorkingCondition = DB::table('person_hope_working_condition')
            ->where('staff_code', $staffCode)
            ->select('hourly_income_min', 'yearly_income_min')
            ->first() ?? (object) ['hourly_income_min' => 0, 'yearly_income_min' => 0];

        if ($personHopeWorkingCondition) {
            $personHopeWorkingCondition->yearly_income_min = $personHopeWorkingCondition->yearly_income_min
                ? intval($personHopeWorkingCondition->yearly_income_min / 10000)
                : null;
        }

        // 📌 ユーザーが選択した都道府県
        $selectedPrefectures = DB::table('person_hope_working_place')
            ->where('staff_code', $staffCode)
            ->pluck('prefecture_code')
            ->toArray();

        // 📌 都道府県一覧
        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->select('code', 'detail')
            ->get();

        // 📌 利用可能なすべてのジョブクラス
        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();

        // 📌 ユーザーが選択したジョブタイプ情報
        $savedJobType = DB::table('person_hope_job_type')
            ->where('staff_code', $staffCode)
            ->select('job_type_code', 'job_type_detail')
            ->first();

        $savedBigClassCode = null;
        $savedMiddleClassCode = null;

        if ($savedJobType) {
            $savedBigClassCode = substr($savedJobType->job_type_code, 0, 2);
            $savedMiddleClassCode = substr($savedJobType->job_type_code, 2, 2);
        }


        // ユーザーが以前に選択したライセンス
        $savedLicenses = DB::table('person_license')
            ->where('staff_code', $staffCode)
            ->get();  // ✅ get() が使用される - コレクションを返す

        if ($savedLicenses->isNotEmpty()) {
            $selectedGroupCode = $savedLicenses->first()->group_code ?? null;
            $selectedCategoryCode = $savedLicenses->first()->category_code ?? null;
            $selectedLicenseCode = $savedLicenses->first()->code ?? null;
        } else {
            $selectedGroupCode = null;
            $selectedCategoryCode = null;
            $selectedLicenseCode = null;
        }

        // **利用可能なライセンス グループをすべて取得する**
        $groups = DB::table('master_license')
            ->select('group_code', 'group_name')
            ->distinct()
            ->get();

        // **選択したグループに一致するカテゴリを取得します**
        $categories = collect();
        if ($selectedGroupCode) {
            $categories = DB::table('master_license')
                ->where('group_code', $selectedGroupCode)
                ->select('category_code', 'category_name')
                ->distinct()
                ->get();
        }

        // **選択したカテゴリのライセンスを取得**
        $licenses = collect();
        if ($selectedGroupCode && $selectedCategoryCode) {
            $licenses = DB::table('master_license')
                ->where('group_code', $selectedGroupCode)
                ->where('category_code', $selectedCategoryCode)
                ->select('code', 'name')
                ->distinct()
                ->get()
                ->unique('code');
        }

        return view('matchings.create', compact(
            'person',
            'personHopeWorkingCondition',
            'selectedPrefectures',
            'prefectures',
            'bigClasses',
            'savedBigClassCode',
            'savedMiddleClassCode',
            'savedJobType',
            'savedLicenses',
            'selectedGroupCode',
            'selectedCategoryCode',
            'selectedLicenseCode',
            'groups',
            'categories',
            'licenses'
        ));
    }
    public function update(Request $request)
    {
        $staffCode = Auth::id();
        Log::info("Update method started. Staff Code: {$staffCode}");

        $request->validate([
            'name' => 'nullable|string',
            'name_f' => 'nullable|string',
            'gender' => 'nullable|in:1,2',
            'birthday' => 'nullable|digits:8',
            'post_u' => 'nullable|digits:3', // ✅ 3桁である必要があります
            'post_l' => 'nullable|digits:4',
            'city' => 'nullable|string',
            'city_f' => 'nullable|string',
            'town' => 'nullable|string',
            'town_f' => 'nullable|string',
            'address' => 'nullable|string',
            'address_f' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'desired_salary_type' => 'nullable|in:年収,時給',
            // 'desired_salary_annual' => 'nullable|numeric|required_if:desired_salary_type,年収',
            // 'desired_salary_hourly' => 'nullable|numeric|required_if:desired_salary_type,時給',
            'desired_salary_annual' => 'sometimes|nullable|numeric|required_if:desired_salary_type,年収',
            'desired_salary_hourly' => 'sometimes|nullable|numeric|required_if:desired_salary_type,時給',
            'prefecture_code' => 'nullable|array',
            'prefecture_code.*' => 'string',
            'big_class_code' => 'nullable|string', // ✅ ユーザーが選択したジョブクラス
            'middle_class_code' => 'nullable|string', // ✅ ユーザーが選択したジョブタイプ
            'group_code' => 'nullable|string|max:10|exists:master_license,group_code',
            'category_code' => 'nullable|string|max:10|exists:master_license,category_code',
            'license_code' => 'nullable|string|max:10|exists:master_license,code',
        ]);

        // ✅ ユーザーが誕生日を入力していない場合は、null に設定されます。
        $birthdayFormatted = $request->filled('birthday')
            ? \Carbon\Carbon::createFromFormat(
                'Ymd',
                $request->birthday
            )->format('Y-m-d 00:00:00')
            : null;

        // ✅ ユーザーが postal_code を入力しなかった場合は、それを null に設定します。
        $postU = $request->filled('post_u') ? $request->post_u : null;
        $postL = $request->filled('post_l') ? $request->post_l : null;
        // ✅ `master_person` を更新します
        DB::table('master_person')
            ->where('staff_code', $staffCode)
            ->update([
                'name' => $request->name ?? null,
                'name_f' => $request->name_f ?? null,
                'sex' => $request->gender ?? null,
                'birthday' => $birthdayFormatted,
                'post_u' => $postU,
                'post_l' => $postL,
                'city' => $request->city ?? null,
                'city_f' => $request->city_f ?? null,
                'town' => $request->town ?? null,
                'town_f' => $request->town_f ?? null,
                'address' => $request->address ?? null,
                'address_f' => $request->address_f ?? null,
                'portable_telephone_number' => $request->phone_number ?? null,
            ]);

        Log::info("Updated master_person table for Staff Code: {$staffCode}");

        // ✅ ユーザーの選択に基づいて給与を更新
        $desiredSalaryAnnual = $request->input('desired_salary_type') === '年収'
            && $request->filled('desired_salary_annual')
            ? intval($request->input('desired_salary_annual')) * 10000
            : 0;

        $desiredSalaryHourly = $request->input('desired_salary_type') === '時給'
            && $request->input('desired_salary_hourly')
            ? $request->input('desired_salary_hourly')
            : 0;

        PersonHopeWorkingCondition::updateSelectiveOrCreate($staffCode, [
            'hourly_income_min' => $desiredSalaryHourly,
            'yearly_income_min' => $desiredSalaryAnnual,
        ]);



        Log::info("Updated person_hope_working_condition for Staff Code: {$staffCode}, Yearly Income: {$desiredSalaryAnnual}, Hourly Income: {$desiredSalaryHourly}");

        // ✅ ユーザーが選択した都道府県情報を更新します
        $prefectureCodes = is_array($request->prefecture_code) ? $request->prefecture_code : [$request->prefecture_code];

        // ✅ 以前のデータを削除する
        DB::table('person_hope_working_place')->where('staff_code', $staffCode)->delete();
        Log::info("Cleared previous entries in person_hope_working_place for Staff Code: {$staffCode}");

        foreach ($prefectureCodes as $prefectureCode) {
            // ✅ 新しい ID を取得します (store メソッドと同様)
            $maxId = DB::table('person_hope_working_place')->where('staff_code', $staffCode)->max('id');
            $newId = $maxId ? $maxId + 1 : 1;

            DB::table('person_hope_working_place')->insert([
                'id' => $newId,
                'staff_code' => $staffCode,
                'prefecture_code' => $prefectureCode,
                'city' => $request->city ?? null,
                'area' => $request->area ?? '日本',
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info("Inserted entry in person_hope_working_place: Staff Code: {$staffCode}, ID: {$newId}, Prefecture Code: {$prefectureCode}");
        }

        Log::info("Updated person_hope_working_place for Staff Code: {$staffCode}");

        // ✅ **新しいコードの追加: ユーザーの job_type を更新します**
        if ($request->filled('big_class_code') && $request->filled('middle_class_code')) {
            $jobTypeCode = $request->big_class_code . $request->middle_class_code . '000';

            // ✅ Middle classの名前を取得する
            $jobTypeDetail = DB::table('master_job_type')
                ->where('big_class_code', $request->big_class_code)
                ->where('middle_class_code', $request->middle_class_code)
                ->value('middle_clas_name');

            if ($jobTypeDetail) {
                DB::table('person_hope_job_type')->updateOrInsert(
                    ['staff_code' => $staffCode],
                    [
                        'job_type_code' => $jobTypeCode,
                        'job_type_detail' => $jobTypeDetail,
                        'update_at' => now(),
                    ],
                );
                Log::info("Updated person_hope_job_type for Staff Code: {$staffCode}, Job Type: {$jobTypeDetail}");
            } else {
                Log::warning("Failed to update job type for Staff Code: {$staffCode}, Job Type not found.");
            }
        } else {
            Log::warning("Job Type update skipped for Staff Code: {$staffCode} - big_class_code or middle_class_code missing.");
        }

        if ($request->filled(['group_code', 'category_code', 'license_code'])) {
            $licenseName = DB::table('master_license')
                ->where('group_code', $request->group_code)
                ->where('category_code', $request->category_code)
                ->where('code', $request->license_code)
                ->value('name');

            if (!$licenseName) {
                return back()->withErrors(['license_code' => '選択したライセンスはデータベースで使用できません!']);
            }

            // ✅ Eski ma'lumotlarni faqat yangi kiritilgan bo'lsa o'chiramiz
            DB::table('person_license')->where('staff_code', $staffCode)->delete();
            Log::info("Cleared previous licenses for Staff Code: {$staffCode}");

            // ✅ Yangi license ma'lumotlarini saqlaymiz
            DB::table('person_license')->insert([
                'staff_code' => $staffCode,
                'group_code' => $request->group_code,
                'category_code' => $request->category_code,
                'code' => $request->license_code,
                'get_day' => now(),
                'remark' => $licenseName,
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info("Inserted new license for Staff Code: {$staffCode}, License Code: {$request->license_code}, License Name: {$licenseName}");
        } else {
            Log::info("No new license selected, keeping old data.");
        }

        //dd($request->all());


        // dd($request->all());
        Log::info("Session message set: 基本情報が変更されました！");
        session()->flash('message', '基本情報が変更されました！');

        return redirect()->route('mypage');
    }
}
