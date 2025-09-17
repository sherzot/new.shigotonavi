<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MatchingUpdateController extends Controller
{
    public function create(Request $request)
    {
        $staffCode = Auth::id();

        // 保存都道府県
        $savedPrefectures = DB::table('person_hope_working_place')
            ->where('staff_code', $staffCode)
            ->pluck('prefecture_code')
            ->toArray();

        // 保存した職業
        $savedJobTypes = DB::table('person_hope_job_type')
            ->join('master_job_type', function ($join) {
                $join->on('person_hope_job_type.job_type_code', '=', DB::raw("CONCAT(master_job_type.big_class_code, master_job_type.middle_class_code, master_job_type.small_class_code)"));
            })
            ->select(
                'person_hope_job_type.job_type_code',
                'master_job_type.middle_clas_name as job_type_detail'
            )
            ->where('person_hope_job_type.staff_code', $staffCode)
            ->get();

        // 希望勤務条件情報
        $savedWorkingCondition = DB::table('person_hope_working_condition')
            ->where('staff_code', $staffCode)
            ->select('hourly_income_min', 'yearly_income_min')
            ->first() ?? (object)['hourly_income_min' => null, 'yearly_income_min' => null];

        // 全国 option
        $allOption = [
            'code' => '000',
            'detail' => '全国',
        ];

        // 業種データの取得
        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();

        // 地域と都道府県の取得
        $regions = DB::table('master_code')->where('category_code', 'Region')->get();
        $prefectures = DB::table('master_code')->where('category_code', 'Prefecture')->get();

        // 地域に基づく都道府県グループ化
        $regionGroups = $regions->map(function ($region) use ($prefectures) {
            return [
                'detail' => $region->detail,
                'prefectures' => $prefectures->filter(function ($prefecture) use ($region) {
                    return $prefecture->region_code === $region->code;
                }),
            ];
        });

        return view('matchings.create', compact(
            'bigClasses',
            'allOption',
            'regionGroups',
            'prefectures',
            'savedPrefectures',
            'savedJobTypes',
            'savedWorkingCondition'
        ));
    }

    public function getJobTypes(Request $request)
    {
        $bigClassCode = $request->input('big_class_code');

        if (!$bigClassCode) {
            return response()->json([], 400); // コードが空の場合はエラーを返します
        }

        $jobTypes = DB::table('master_job_type')
            ->where('big_class_code', $bigClassCode)
            ->select('middle_class_code', 'middle_clas_name') // データの選択
            ->get();

        return response()->json($jobTypes);
    }

    public function update()
    {
        $staffCode = Auth::id();

        // 保存したライセンスを取得する
        $savedLicense = DB::table('person_license')
            ->where('staff_code', $staffCode)
            ->select('group_code', 'category_code', 'code')
            ->first();

        $selectedGroupName = null;
        $selectedCategoryName = null;
        $selectedLicenseName = null;

        // 保存されたライセンスの詳細の名前を取得します (savedLicense が null でない場合のみ)
        if ($savedLicense) {
            $selectedGroupName = DB::table('master_license')
                ->where('group_code', $savedLicense->group_code)
                ->value('group_name');

            $selectedCategoryName = DB::table('master_license')
                ->where('group_code', $savedLicense->group_code)
                ->where('category_code', $savedLicense->category_code)
                ->value('category_name');

            $selectedLicenseName = DB::table('master_license')
                ->where('group_code', $savedLicense->group_code)
                ->where('category_code', $savedLicense->category_code)
                ->where('code', $savedLicense->code)
                ->value('name');
        }

        // big classesを取得する
        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();

        // 保存した都道府県を取得する
        $savedPrefectures = DB::table('person_hope_working_place')
            ->where('staff_code', $staffCode)
            ->pluck('prefecture_code')
            ->toArray();

        // person_hope_job_type テーブルから保存されたジョブタイプの詳細を取得します。
        $savedJobType = DB::table('person_hope_job_type')
            ->where('staff_code', $staffCode)
            ->select('job_type_code', 'job_type_detail')
            ->first();

        $savedBigClassCode = null;
        $savedMiddleClassCode = null;

        if ($savedJobType) {
            $savedBigClassCode = substr($savedJobType->job_type_code, 0, 2); // Extract big_class_code
            $savedMiddleClassCode = substr($savedJobType->job_type_code, 2, 2); // Extract middle_class_code
        }
        // Fetch prefectures
        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->select('code', 'detail')
            ->get();

        // 保存された動作状態を取得する
        $savedWorkingCondition = DB::table('person_hope_working_condition')
            ->where('staff_code', $staffCode)
            ->select('hourly_income_min', 'yearly_income_min')
            ->first();

        // Checkbox options
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
            //'find_job_festive_flag' => '就職祝金',
            'appointment_flag' => '正社員登用',
        ];

        return view('matchings.update', compact(
            'bigClasses',
            'prefectures',
            'savedPrefectures',
            'savedJobType',
            'savedWorkingCondition',
            'selectedGroupName',
            'selectedCategoryName',
            'selectedLicenseName',
            'savedLicense',
            'savedBigClassCode',
            'savedMiddleClassCode',
            'checkboxOptions'
        ));
    }

    public function updateResults(Request $request)
    {
        $staffCode = Auth::id();
        Log::info("=====================================");
        Log::info("📌 [MATCHING QUERY START] - " . now());
        Log::info("=====================================");

        Log::info('📌 Staff Code:', ['staff_code' => $staffCode]);

        // 📌 Prefecture Codes (ユーザーが選択または保存したデータ)
        $prefectureCodes = $request->input('prefecture_code', []);
        if (empty($prefectureCodes)) {
            $prefectureCodes = DB::table('person_hope_working_place')
                ->where('staff_code', $staffCode)
                ->pluck('prefecture_code')
                ->toArray();
        }
        $prefectureCodes = array_map('strval', $prefectureCodes);
        Log::info('📌 Prefecture Codes:', ['codes' => $prefectureCodes]);

        if (empty($prefectureCodes)) {
            return redirect()->route('matchings.updateForm')->withErrors(['msg' => '正しい都道府県を選択してください。']);
        }

        // 📌 Job Types (ユーザーが選択または保存したデータ)
        $personHopeJobTypesCode = $request->input('job_type_code', []);

        if (empty($personHopeJobTypesCode)) {
            $personHopeJobTypesCode = DB::table('person_hope_job_type')
                ->where('staff_code', $staffCode)
                ->pluck('job_type_code')
                ->toArray();
        }

        $bigClassCode = $request->input('big_class_code');
        $middleClassCode = $request->input('middle_class_code');

        $combinedClassCode = $bigClassCode . $middleClassCode . '000';

        if (empty($bigClassCode) || empty($middleClassCode)) {
            $firstJobTypeCode = DB::table('person_hope_job_type')
                ->where('staff_code', $staffCode)
                ->value('job_type_code');

            if ($firstJobTypeCode) {
                $bigClassCode = substr($firstJobTypeCode, 0, 2);
                $middleClassCode = substr($firstJobTypeCode, 2, 2);
                $combinedClassCode = $bigClassCode . $middleClassCode . '000';
            }
        }

        $personHopeJobTypesCode = array_map('strval', $personHopeJobTypesCode);
        Log::info('📌 Person Hope Job Types Code:', ['codes' => $personHopeJobTypesCode]);

        // 📌 給与の種類と金額 (ユーザーが選択しましたが、データベースには保存されていません)
        $desiredSalaryType = $request->input('desired_salary_type');
        $desiredSalary = 0;

        if ($desiredSalaryType === '年収') {
            $desiredSalary = (float) ($request->input('desired_salary_annual') ?? 0) * 10000;
        } elseif ($desiredSalaryType === '時給') {
            $desiredSalary = (float) ($request->input('desired_salary_hourly') ?? 0);
        }

        if ($desiredSalary <= 0) {
            return redirect()->route('matchings.updateForm')->withErrors(['msg' => '正しい給与金額を入力してください。']);
        }

        Log::info('📌 Selected Salary Type:', ['type' => $desiredSalaryType]);
        Log::info('📌 Desired Salary:', ['salary' => $desiredSalary]);

        // 🎯 ユーザー年齢の計算
        $personBirthDay = DB::table('master_person')
            ->where('staff_code', $staffCode)
            ->select('birthday')
            ->first();
        $personAge = $personBirthDay ? Carbon::parse($personBirthDay->birthday)->age : null;
        Log::info("📌 User Age:", ['age' => $personAge]);

        $selectedFlags = $request->input('supplement_flags', []);
        $checkboxOptions = $this->checkboxOptions();
        Log::info("📌 Selected Supplement Flags:", $selectedFlags);

        // 📌 クエリ実行を開始
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
            ->leftJoin('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
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

            ->when(!empty($personHopeJobTypesCode) || !empty($combinedClassCode), function ($query) use ($personHopeJobTypesCode, $combinedClassCode) {
                $query->where(function ($subQuery) use ($personHopeJobTypesCode, $combinedClassCode) {
                    if (!empty($combinedClassCode)) {
                        $subQuery->orWhere('job_job_type.job_type_code', $combinedClassCode);
                    }
                    // if (!empty($personHopeJobTypesCode)) {
                    //     $subQuery->whereIn('job_job_type.job_type_code', $personHopeJobTypesCode);
                    // }
                    
                });
            })
            ->when(!empty($prefectureCodes), function ($query) use ($prefectureCodes) {
                return $query->whereIn('job_working_place.prefecture_code', $prefectureCodes);
            })
            ->when($desiredSalaryType === '時給' && !is_null($desiredSalary) && $desiredSalary > 0, function ($query) use ($desiredSalary) {
                Log::info("📌 Filtering jobs based on hourly salary:", ['hourly_income_min' => $desiredSalary]);
                return $query->whereRaw('CAST(NULLIF(job_order.hourly_income_min, "0") AS SIGNED) >= ?', [(int) $desiredSalary]);
            })
            ->when($desiredSalaryType === '年収' && !is_null($desiredSalary) && $desiredSalary > 0, function ($query) use ($desiredSalary) {
                Log::info("📌 Filtering jobs based on yearly salary:", ['yearly_income_min' => $desiredSalary]);
                return $query->whereRaw('CAST(NULLIF(job_order.yearly_income_min, "0") AS SIGNED) >= ?', [(int) $desiredSalary]);
            })


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
                'job_supplement_info.pr_contents1'
            )
            ->orderBy('job_order.update_at', 'desc')
            ->distinct()
            ->paginate(6)
            ->appends($request->all());

        Log::info('📌 Query Log:', DB::getQueryLog());
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
        foreach ($matchingResults as $result) {
            $result->yearly_income_display = "{$result->yearly_income_min}円〜" . ($result->yearly_income_max > 0 ? "{$result->yearly_income_max}円" : '');
            $result->hourly_income_display = "{$result->hourly_income_min}円〜" . ($result->hourly_income_max > 0 ? "{$result->hourly_income_max}円" : '');
        }

        Log::info('📌 Salary Formatting Completed');
        return view('matchings.results', compact('matchingResults', 'selectedFlags', 'checkboxOptions', 'desiredSalaryType'));
    }


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
            'land_shop_flag' => '店舗提供',
            'find_job_festive_flag' => '就職祝金',
            'appointment_flag' => '正社員登録',
            'license_acquisition_support_flag' => '資格取得支援あり'
        ];
    }
}
