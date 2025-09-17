<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PublicMatchingController extends Controller
{
    private function checkboxOptions()
    {
        return [
            'inexperienced_person_flag' => '未経験者OK',
            'balancing_work_flag' => '仕事と生活のバランス',
            'ui_turn_flag' => 'UIターン',
            'many_holiday_flag' => '休日120日',
            'flex_time_flag' => 'フルリモート',
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
            //'find_job_festive_flag' => '就職祝金',
            'appointment_flag' => '正社員登録',
            'license_acquisition_support_flag' => '資格取得支援あり'
        ];
    }

    public function getJobCategories($big_class_code)
    {
        try {
            return response()->json(
                DB::table('master_job_type')
                    ->where('big_class_code', $big_class_code)
                    ->where('middle_class_code', '!=', '00')
                    ->select('middle_class_code', 'middle_clas_name')
                    ->orderBy('middle_class_code')
                    ->get()
            );
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function storeInitialSearch(Request $request)
    {
        session([
            'search.big_class_code' => $request->input('big_class_code'),
            'search.job_category' => $request->input('job_category'),
        ]);
        return response()->json(['status' => 'saved']);
    }


    public function index(Request $request)
    {
        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();
        $checkboxOptions = $this->checkboxOptions();
        // 地域と都道府県を取得する
        $regions = DB::table('master_code')
            ->where('category_code', 'Region')
            ->get();

        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->get();

        // 地域に属する都道府県のグループ化
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

        $selectedPrefectures = [];

        $jobType = $request->input('job_type'); // eski nom
        $keyword = $request->input('keyword'); // キーワード
        $salary = $request->input('salary');
        $bigClassCode = $request->input('big_class_code');
        $location = $request->input('prefecture_code');
        $certificate = $request->input('certificate');
        $supplementFlags = $request->input('supplement_flags', []);

        $jobs = null;

        // フォームから検索条件があればクエリを実行する
        if ($keyword || $jobType || $bigClassCode) {
            $jobs = DB::table('job_order')
                ->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
                ->join('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
                ->join('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
                ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
                ->join('master_code', function ($join) {
                    $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                        ->where('master_code.category_code', '=', 'Prefecture');
                })
                ->when($keyword, fn($q) => $q->where('job_order.job_type_detail', 'like', "%{$keyword}%"))
                ->when($jobType, fn($q) => $q->where('job_order.job_type_detail', 'like', "%{$jobType}%"))
                ->when($request->filled('big_class_code') && $request->filled('job_category'), function ($q) use ($request) {
                    $targetCode = $request->input('big_class_code') . $request->input('job_category') . '000';
                    $q->where('job_job_type.job_type_code', $targetCode);
                })
                ->when($request->filled('big_class_code') && !$request->filled('job_category'), function ($q) use ($request) {
                    $prefix = $request->input('big_class_code');
                    $q->where('job_job_type.job_type_code', 'like', $prefix . '%');
                })

                ->when($salary, fn($q) => $q->where(function ($q2) use ($salary) {
                    $q2->where('job_order.yearly_income_min', '>=', $salary * 10000)
                        ->orWhere('job_order.hourly_income_min', '>=', $salary);
                }))
                ->when($location, fn($q) => $q->where('job_working_place.prefecture_code', $location))
                ->when($certificate, fn($q) => $q->where('job_order.job_type_detail', 'like', "%{$certificate}%"))
                ->when(!empty($supplementFlags), function ($q) use ($supplementFlags) {
                    foreach ($supplementFlags as $flag) {
                        $q->where("job_supplement_info.{$flag}", 1);
                    }
                })
                ->where('job_order.public_flag', 1)
                ->where('job_order.order_progress_type', 1)
                ->where('job_order.public_limit_day', '>=', now())
                ->select([
                    'job_order.order_code as id',
                    'job_order.job_type_detail',
                    'job_order.yearly_income_min',
                    'job_order.yearly_income_max',
                    'job_order.hourly_income_min',
                    'job_order.hourly_income_max',
                    'job_supplement_info.pr_title1',
                    'job_supplement_info.pr_contents1',
                    'master_company.company_name_k',
                    'master_code.detail as prefecture_name',
                    'job_order.update_at',
                    // 'job_job_type.big_class_code',
                    // 'job_job_type.middle_class_code'
                ])
                ->addSelect(array_keys($checkboxOptions))
                ->groupBy(
                    'job_order.order_code',
                    'job_order.job_type_detail',
                    'job_order.yearly_income_min',
                    'job_order.yearly_income_max',
                    'job_order.hourly_income_min',
                    'job_order.hourly_income_max',
                    'job_supplement_info.pr_title1',
                    'job_supplement_info.pr_contents1',
                    'master_company.company_name_k',
                    'master_code.detail',
                    'job_working_place.city',
                    'job_working_place.town',
                    'job_job_type.job_type_code',
                    // 'job_job_type.big_class_code',
                    // 'job_job_type.middle_class_code'
                )
                ->orderByDesc('job_order.update_at')
                ->distinct()
                ->paginate(6)
                ->appends($request->all());

            // 追加フィールドのフォーマット
            foreach ($jobs as $job) {
                $job->selectedFlagsArray = [];
                foreach ($checkboxOptions as $key => $label) {
                    if (!empty($job->$key) && $job->$key == 1) {
                        $job->selectedFlagsArray[] = $label;
                    }
                }

                $job->yearly_income_display = $job->yearly_income_min . '円〜' . ($job->yearly_income_max > 0 ? "{$job->yearly_income_max}円" : '');
                $job->hourly_income_display = $job->hourly_income_min . '円〜' . ($job->hourly_income_max > 0 ? "{$job->hourly_income_max}円" : '');
            }
        }
        $jobCount = DB::table('job_order')
            ->where('public_flag', 1)
            ->where('order_progress_type', 1)
            ->where('public_limit_day', '>=', now())
            ->distinct('order_code')
            ->count('order_code');
        $companyCount = DB::table('master_company')->count('company_code');
        $userCount = DB::table('master_person')->count('staff_code');


        return view('matchings.match', compact(
            'jobs',
            'checkboxOptions',
            'bigClasses',
            'prefectures',
            'regionGroups', // 地域
            'individualPrefectures', // 各都道府県
            'selectedPrefectures',
            'jobCount',         // 🔹 追加
            'companyCount',     // 🔹 追加
            'userCount'         // 🔹 追加
        ));
    }
    public function detail($id)
    {
        $job = DB::table('job_order')->where('order_code', $id)->first();

        $staffCode = optional(Auth::user())->staff_code;

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
                'job_supplement_info.process1',
                'job_supplement_info.process2',
                'job_supplement_info.process3',
                'job_supplement_info.process4',
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

        // 仕事が見つからない場合
        if (!$job) {
            return redirect()->route('matchings.match')->withErrors(['msg' => '求人の詳細は見つかりませんでした。']);
        }
        $jobTypes = DB::table('job_job_type')
            ->join('master_job_type', 'job_job_type.job_type_code', '=', 'master_job_type.all_connect_code')
            ->where('job_job_type.order_code', $id)
            ->select('master_job_type.big_class_name', 'master_job_type.middle_clas_name')
            ->get();
        $company = DB::table('master_company')
            ->where('company_code', $job->company_code)
            ->select('industry_type_name', 'business_contents', 'capital_amount', 'all_employee_num')
            ->first();
        

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
        $workingTime = DB::table('job_working_condition')
            ->where('job_working_condition.order_code', $id)
            ->first();

        if (!$job) {
            abort(404);
        }
        if ($staffCode && $job) {
            // log_access_history_staff
            DB::table('log_access_history_staff')->updateOrInsert(
                [
                    'staff_code' => $staffCode,
                    'order_code' => $id,
                ],
                [
                    'created_at' => now(),
                    'update_at' => now(),
                ]
            );


            // update detail_count in log_person_signin
            $currentCount = DB::table('log_person_signin')
                ->where('staff_code', $staffCode)
                ->value('detail_count');

            DB::table('log_person_signin')->updateOrInsert(
                ['staff_code' => $staffCode],
                [
                    'detail_count' => ($currentCount ?? 0) + 1,
                    'last_viewed_job' => $id,
                    'update_at' => now(),
                ]
            );
        }
        // if (!Auth::check()) {
        //     session()->put('apply_job', $id);
        // }
        session()->put('apply_job', $id);

        // return view('matchings.job_detail', compact('job'));
        return view('matchings.job_detail', compact(
            'job',
            'checkboxOptions',
            'selectedFlagsArray',
            'desiredSalaryType',
            'prefecturesArray',
            'workingTime',
            'locations',
            'jobTypes',
            'company'
        ));
    }
}
