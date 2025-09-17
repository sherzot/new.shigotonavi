<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class PublicJobSearch extends Component
{
    use WithPagination;

    // public $keyword = '';
    public int $jobCount = 0;
    public int $companyCount = 0;
    public int $userCount = 0;

    public $salary = '';
    public $hourly_wage = '';
    public $location = '';
    public $certificate = '';
    public $big_class_code = '';
    public $job_category = '';
    public $jobCategories = [];
    public $supplementFlags = [];
    public $hasSearched = false;
    public $jobs = [];
    public $filters = [];
    public $matchCount = 0;
    public $updateCount = 0;

    public function mount()
    {
        $this->jobCount = DB::table('job_order')
            ->where('public_flag', 1)
            ->where('order_progress_type', 1)
            ->where('public_limit_day', '>=', now())
            ->distinct('order_code')
            ->count('order_code');
        $this->companyCount = DB::table('master_company')->count('company_code');
        $this->userCount = DB::table('master_person')->count('staff_code');
        // $this->keyword = session('search.keyword', '');
        $this->salary = session('search.salary', '');
        $this->hourly_wage = session('search.hourly_wage', '');
        $this->location = session('search.location', '');
        $this->certificate = session('search.certificate', '');
        $this->big_class_code = session('search.big_class_code', '');
        $this->job_category = session('search.job_category', '');
        $this->supplementFlags = session('search.supplementFlags', []);
        if (!empty($this->big_class_code)) {
            $this->jobCategories = DB::table('master_job_type')
                ->where('big_class_code', $this->big_class_code)
                ->select('middle_class_code', 'middle_clas_name')
                ->get()
                ->toArray();
        }
        $this->matchCount = 0;
        $this->updateCount = 0;

        // $this->hasSearched = !empty($this->keyword) || !empty($this->big_class_code) || !empty($this->salary) || !empty($this->location) || !empty($this->certificate);
        $this->hasSearched = !empty($this->big_class_code) || !empty($this->job_category) || !empty($this->salary) || !empty($this->location) || !empty($this->certificate);
    }
    public function updated($propertyName)
    {
        if ($propertyName === 'big_class_code') {
            $this->jobCategories = DB::table('master_job_type')
                ->where('big_class_code', $this->big_class_code)
                ->select('middle_class_code', 'middle_clas_name')
                ->get()
                ->toArray();
            $this->job_category = '';
        }

        $this->resetPage();
    }
    private function loadJobCategories($bigClassCode)
    {
        $this->jobCategories = json_decode(json_encode(
            DB::table('master_job_type')
                ->where('big_class_code', $value)
                ->where('middle_class_code', '!=', '00')
                ->select('middle_class_code', 'middle_clas_name')
                ->orderBy('middle_class_code')
                ->get()
        ), true);
    }
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
            'find_job_festive_flag' => '就職祝金',
            'license_acquisition_support_flag' => '資格取得支援あり'
        ];
    }

    public function searchJobs()
    {
        $staffCode = optional(Auth::user())->staff_code;
        $this->hasSearched = true;
        // 🔍 Top-level search (職種 + タイプ)
        $isFirstSearch = !empty($this->big_class_code) || !empty($this->job_category);
        $ip = Request::ip();
        $userAgent = Request::header('User-Agent');

        Log::channel('job_search_log')->info('🔍 JOB SEARCH 条件ログ:', [
            'IPアドレス' => $ip,
            '端末情報' => $userAgent,
            '職種コード' => $this->big_class_code,
            '職種タイプ' => $this->job_category,
            '希望年収(万円)' => $this->salary,
            '希望時給' => $this->hourly_wage,
            '勤務地' => $this->location,
            '資格' => $this->certificate,
            '補足フラグ' => $this->supplementFlags,
            '検索時間' => now()->toDateTimeString(),
        ]);
        session([
            // 'search.keyword' => $this->keyword,
            'search.salary' => $this->salary,
            'search.hourly_wage' => $this->hourly_wage,
            'search.location' => $this->location,
            'search.certificate' => $this->certificate,
            'search.big_class_code' => $this->big_class_code,
            'search.job_category' => $this->job_category,
            'search.supplementFlags' => $this->supplementFlags,
        ]);
        // 1. Top initial matching jobs
        // $this->jobs = DB::table('job_order')->where('public_flag', 1)->get();
        // 🔍 matchCount — bu: top-level search (職種 + タイプ)
        $allMatches = $this->getJobsProperty();

        $this->matchCount = $isFirstSearch ? $allMatches->count() : $this->matchCount;// <-- to‘g‘ri hisoblanadi
        $this->updateCount = $allMatches->count(); // to‘g‘rilik uchun qo‘shamiz
        $this->jobs = $allMatches; // filtrlanganlar ham shu paytda hisoblanadi

        // 2. Update match_count in log_person_signin
        // ✅ DB log
        if ($staffCode) {
            $data = [
                'update_count' => $this->updateCount,
                'filter_salary' => $this->salary ? $this->salary * 10000 : null,
                'filter_hourly_wage' => $this->hourly_wage,
                'filter_location' => $this->location,
                'filter_flags' => json_encode($this->supplementFlags),
                'update_at' => now(),
                'mypage_at' => now(),
            ];

            if ($isFirstSearch) {
                $data['match_count'] = $this->matchCount;
                $data['search_big_class_code'] = $this->big_class_code;
                $data['search_job_category'] = $this->job_category;
            }

            DB::table('log_person_signin')->updateOrInsert(
                ['staff_code' => $staffCode],
                $data
            );
        }       
    }

    public function getJobsProperty()
    {
        $staffCode = optional(Auth::user())->staff_code;

        // 1️⃣ FILTERS ni to‘liq aniqlaymiz
        $filters = [
            'salary' => $this->salary,
            'hourly_wage' => $this->hourly_wage,
            'location' => $this->location,
            'certificate' => $this->certificate,
        ];

        // 2️⃣ filters ni saqlab qo‘yamiz (agar view-da kerak bo‘lsa)
        $this->filters = $filters;

        // 3️⃣ Bazaviy so‘rov
        $query = DB::table('job_order')
            ->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
            ->join('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
            ->join('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
            ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
            ->join('master_code', function ($join) {
                $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                    ->where('master_code.category_code', '=', 'Prefecture');
            });

        // 🔍 FILTER 1: Job Type
        if ($this->big_class_code && $this->job_category) {
            $code = $this->big_class_code . $this->job_category . '000';
            $query->where('job_job_type.job_type_code', $code);
        } elseif ($this->big_class_code) {
            $query->where('job_job_type.job_type_code', 'like', $this->big_class_code . '%');
        }

        // 🔍 FILTER 2: Salary or Hourly Wage
        if ($filters['salary']) {
            $salaryYen = $filters['salary'] * 10000;
            $query->where(function ($q) use ($salaryYen) {
                $q->where('job_order.yearly_income_min', '<=', $salaryYen)
                    ->where('job_order.yearly_income_max', '>=', $salaryYen);
            });
        } elseif ($filters['hourly_wage']) {
            $hourlyYen = $filters['hourly_wage'];
            $query->where(function ($q) use ($hourlyYen) {
                $q->where('job_order.hourly_income_min', '<=', $hourlyYen)
                    ->where('job_order.hourly_income_max', '>=', $hourlyYen);
            });
        }

        // 🔍 FILTER 3: Location
        if ($filters['location']) {
            $query->where('master_code.detail', $filters['location']);
        }

        // 🔍 FILTER 4: Certificate (職種詳細に文字が入っている bo‘lsa)
        if ($filters['certificate']) {
            $query->where('job_order.job_type_detail', 'like', '%' . $filters['certificate'] . '%');
        }

        // 🔍 FILTER 5: Supplement Flags (checkboxlar)
        foreach ($this->supplementFlags as $flag) {
            $query->where($flag, 1);
        }

        // 4️⃣ Ma'lumotlarni olish
        $results = $query->select([
            'job_order.order_code as id',
            'job_order.job_type_detail',
            'job_order.yearly_income_min',
            'job_order.yearly_income_max',
            'job_order.monthly_income_min',
            'job_order.monthly_income_max',
            'job_order.hourly_income_min',
            'job_order.hourly_income_max',
            'job_supplement_info.pr_title1',
            'master_company.company_name_k',
            DB::raw("GROUP_CONCAT(DISTINCT master_code.detail SEPARATOR ', ') as prefecture_names"),
            'job_job_type.job_type_code',
            'job_order.created_at',
            'job_order.order_type',
            'job_order.business_detail',
        ])
            ->addSelect(array_keys($this->checkboxOptions()))
            ->where('job_order.public_flag', 1)
            ->where('job_order.order_progress_type', 1)
            ->where('job_order.public_limit_day', '>=', now())
            ->orderByDesc('job_order.update_at')
            ->groupBy('job_order.order_code')
            ->get();

        // 5️⃣ Frontend uchun qo‘shimcha belgilar
        foreach ($results as $job) {
            $job->selectedFlagsArray = collect($this->checkboxOptions())
                ->filter(fn($_, $key) => !empty($job->$key) && $job->$key == 1)
                ->values()
                ->toArray();

            $job->employment_label = match ($job->order_type) {
                1 => '派遣',
                2 => '正社員',
                3 => '契約社員',
                default => '',
            };
        }

        // 6️⃣ Faqat $filters asosida filtrlanganlarni saqlash (optional)
        $filtered = collect($results);

        if ($filters['salary']) {
            $filtered = $filtered->where('yearly_income_min', '>=', $filters['salary'] * 10000);
        }

        if ($filters['location']) {
            $filtered = $filtered->filter(fn($job) => str_contains($job->prefecture_names, $filters['location']));
        }

        $this->jobs = $filtered;
        $this->updateCount = $filtered->count();

        if ($staffCode) {
            DB::table('log_person_signin')
                ->where('staff_code', $staffCode)
                ->update([
                    'update_count' => $this->updateCount,
                    'filter_salary' => $this->salary ? $this->salary * 10000 : null,
                    'filter_hourly_wage' => $this->hourly_wage,
                    'filter_location' => $this->location,
                    'filter_flags' => json_encode($this->supplementFlags),
                    'update_at' => now(),
                ]);
        }            
        return $filtered;
    }

    public function render()
    {
        $jobList = $this->getJobsProperty();

        // 🔎 表示中の求人情報をログに記録
        Log::channel('job_search_log')->info('📊 求人票の表示:', [
            '表示件数' => $jobList->count(),
            '検索済みか' => $this->hasSearched ? 'はい' : 'いいえ',
            '表示時刻' => now()->toDateTimeString(),
        ]);

        $middleClassMap = DB::table('master_job_type')
            ->pluck('middle_clas_name', DB::raw("CONCAT(big_class_code, middle_class_code, '000')"))
            ->toArray();

        return view('livewire.public-job-search', [
            'bigClasses' => DB::table('master_job_type')
                ->select('big_class_code', 'big_class_name')
                ->distinct()
                ->get(),
            'prefectures' => DB::table('master_code')
                ->where('category_code', 'Prefecture')
                ->pluck('detail'),
            'jobs' => $jobList,
            'middleClassMap' => $middleClassMap,
            'checkboxOptions' => $this->checkboxOptions(),
            'jobCount' => $this->jobCount,
            'companyCount' => $this->companyCount,
            'userCount' => $this->userCount,
            'job' => $this->jobs,
        ]);
    }
}
