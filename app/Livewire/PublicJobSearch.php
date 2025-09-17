<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

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
        $this->big_class_code = session('search.big_class_code', '');
        $this->job_category = session('search.job_category', '');
        $this->supplementFlags = session('search.supplementFlags', []);
        if (!empty($this->big_class_code)) {
            $this->jobCategories = DB::table('master_job_type')
                ->where('big_class_code', $this->big_class_code)
                ->select('middle_class_code', 'middle_clas_name')
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray();
        }
        $this->matchCount = 0;
        $this->updateCount = 0;

        // $this->hasSearched = !empty($this->keyword) || !empty($this->big_class_code) || !empty($this->salary) || !empty($this->location) || !empty($this->certificate);
        $this->hasSearched = !empty($this->big_class_code) || !empty($this->job_category) || !empty($this->salary) || !empty($this->location) || !empty($this->certificate);
        Log::channel('job_search_log')->info('🔧 mount() chaqirildi', [
            'jobCount' => $this->jobCount,
            'companyCount' => $this->companyCount,
            'userCount' => $this->userCount,
            'Session search' => session('search')
        ]);
    }

    public function submitInitialSearch()
    {
        $staffCode = optional(Auth::user())->staff_code;

        $results = $this->getJobsProperty();
        $this->matchCount = $results->count();
        $this->updateCount = $this->matchCount;
        $this->jobs = $results;
        $this->hasSearched = true;

        if ($staffCode) {
            DB::table('log_person_signin')->updateOrInsert(
                ['staff_code' => $staffCode],
                [
                    'match_count' => $this->matchCount,
                    'update_count' => $this->updateCount,
                    'search_big_class_code' => $this->big_class_code,
                    'search_job_category' => $this->job_category,
                    'filter_salary' => $this->salary ? $this->salary * 10000 : null,
                    'filter_hourly_wage' => $this->hourly_wage,
                    'filter_location' => $this->location,
                    'filter_flags' => json_encode($this->supplementFlags),
                    'mypage_at' => now(),
                    'update_at' => now(),
                ]
            );
        }

        Log::info('submitInitialSearch', [
            'selected_big_class_code' => $this->big_class_code,
            'selected_job_category' => $this->job_category,
        ]);
        Log::channel('job_search_log')->info('🚀 初期検索 submitInitialSearch 実行', [
            'staff_code' => $staffCode,
            'match_count' => $this->matchCount,
            'update_count' => $this->updateCount,
            '保存済みセッション' => session()->all()
        ]);
    }

    public function updated($property)
    {
        Log::channel('job_search_log')->info("✏️ updated() メソッド: {$property}が更新された", [
            '新しい値' => $this->$property
        ]);
        if ($property === 'big_class_code') {
            $this->loadJobCategories();
            $this->job_category = '';
            session()->put('search.big_class_code', $this->big_class_code);
        }
        if ($property === 'job_category') {
            session()->put('search.job_category', $this->job_category);
        }
        $this->resetPage();
    }

    public function loadJobCategories()
    {
        $this->jobCategories = DB::table('master_job_type')
            ->where('big_class_code', $this->big_class_code)
            ->where('middle_class_code', '!=', '00')
            ->select('middle_class_code', 'middle_clas_name')
            ->orderBy('middle_class_code')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
        Log::channel('job_search_log')->info('📥 loadJobCategories 実行', [
            '選択された職種コード' => $this->big_class_code,
            '読み込まれた職種タイプ数' => count($this->jobCategories)
        ]);
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
        $staffCode = optional(Auth::user())->staff_code;
        $results = $this->getJobsProperty();
        $this->jobs = $results;
        $this->updateCount = $results->count();
        $this->hasSearched = true;

        // 2. Update match_count in log_person_signin
        // ✅ DB log
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
        Log::channel('job_search_log')->info('🔁 searchJobs 実行完了', [
            'staff_code' => $staffCode,
            '該当求人数' => $this->updateCount
        ]);        
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
        $personAge = null;
        if ($staffCode) {
            $birthday = DB::table('master_person')
                ->where('staff_code', $staffCode)
                ->value('birthday');

            if ($birthday) {
                $personAge = \Carbon\Carbon::parse($birthday)->age;
            }
        }

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
        // 🔍 FILTER 1.9: Maosh qiymati mavjud bo‘lgan ishlarni olish (filter bo‘lmasa ham)
        $query->where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereNotNull('job_order.yearly_income_min')->where('job_order.yearly_income_min', '>', 0);
            })->orWhere(function ($inner) {
                $inner->whereNotNull('job_order.yearly_income_max')->where('job_order.yearly_income_max', '>', 0);
            })->orWhere(function ($inner) {
                $inner->whereNotNull('job_order.hourly_income_min')->where('job_order.hourly_income_min', '>', 0);
            })->orWhere(function ($inner) {
                $inner->whereNotNull('job_order.hourly_income_max')->where('job_order.hourly_income_max', '>', 0);
            });
        });

        // 🔍 FILTER 2: Yillik maosh yoki Soatlik ish haqi
        if (!empty($filters['salary'])) {
            $salaryYen = $filters['salary'] * 10000;

            // 👉 Faqat meaningful yillik maoshga ega ishlar
            $query->where(function ($q) use ($salaryYen) {
                $q->where(function ($q2) use ($salaryYen) {
                    // ✅ oraliqda: min <= salary <= max
                    $q2->where('job_order.yearly_income_min', '<=', $salaryYen)
                        ->where('job_order.yearly_income_max', '>=', $salaryYen);
                })
                    ->orWhere(function ($q2) use ($salaryYen) {
                        // ✅ faqat min bor, max yo'q
                        $q2->where('job_order.yearly_income_min', '<=', $salaryYen)
                            ->where(function ($q3) {
                                $q3->whereNull('job_order.yearly_income_max')
                                    ->orWhere('job_order.yearly_income_max', 0);
                            })
                            ->where('job_order.yearly_income_min', '>', 0);
                    })
                    ->orWhere(function ($q2) use ($salaryYen) {
                        // ✅ faqat max bor, min yo'q
                        $q2->where('job_order.yearly_income_max', '>=', $salaryYen)
                            ->where(function ($q3) {
                                $q3->whereNull('job_order.yearly_income_min')
                                    ->orWhere('job_order.yearly_income_min', 0);
                            })
                            ->where('job_order.yearly_income_max', '>', 0);
                    })
                    ->orWhere(function ($q2) use ($salaryYen) {
                        // ✅ ish taklifi foydalanuvchining maoshidan yuqori bo‘lsa ham chiqsin
                        $q2->where('job_order.yearly_income_min', '>=', $salaryYen)
                            ->where('job_order.yearly_income_min', '>', 0);
                    });
            });
        } elseif (!empty($filters['hourly_wage'])) {
            $hourlyYen = $filters['hourly_wage'];

            // 👉 Faqat meaningful soatlik maoshga ega ishlar
            $query->where(function ($q) use ($hourlyYen) {
                $q->where(function ($q2) use ($hourlyYen) {
                    // ✅ oraliqda: min <= wage <= max
                    $q2->where('job_order.hourly_income_min', '<=', $hourlyYen)
                        ->where('job_order.hourly_income_max', '>=', $hourlyYen);
                })
                    ->orWhere(function ($q2) use ($hourlyYen) {
                        // ✅ faqat min bor, max yo'q
                        $q2->where('job_order.hourly_income_min', '<=', $hourlyYen)
                            ->where(function ($q3) {
                                $q3->whereNull('job_order.hourly_income_max')
                                    ->orWhere('job_order.hourly_income_max', 0);
                            })
                            ->where('job_order.hourly_income_min', '>', 0);
                    })
                    ->orWhere(function ($q2) use ($hourlyYen) {
                        // ✅ faqat max bor, min yo'q
                        $q2->where('job_order.hourly_income_max', '>=', $hourlyYen)
                            ->where(function ($q3) {
                                $q3->whereNull('job_order.hourly_income_min')
                                    ->orWhere('job_order.hourly_income_min', 0);
                            })
                            ->where('job_order.hourly_income_max', '>', 0);
                    })
                    ->orWhere(function ($q2) use ($hourlyYen) {
                        // ✅ ish taklifi foydalanuvchining maoshidan yuqori bo‘lsa ham chiqsin
                        $q2->where('job_order.hourly_income_min', '>=', $hourlyYen)
                            ->where('job_order.hourly_income_min', '>', 0);
                    });
            });
        }

        if ($personAge !== null) {
            $query->where(function ($q) use ($personAge) {
                // 1. age_min <= age <= age_max
                $q->where(function ($q2) use ($personAge) {
                    $q2->where('job_order.age_min', '<=', $personAge)
                        ->where('job_order.age_max', '>=', $personAge);
                })
                    // 2. Faqat min bor, max yo'q
                    ->orWhere(function ($q2) use ($personAge) {
                        $q2->where('job_order.age_min', '<=', $personAge)
                            ->where(function ($q3) {
                                $q3->whereNull('job_order.age_max')
                                    ->orWhere('job_order.age_max', 0);
                            })
                            ->where('job_order.age_min', '>', 0);
                    })
                    // 3. Faqat max bor, min yo'q
                    ->orWhere(function ($q2) use ($personAge) {
                        $q2->where('job_order.age_max', '>=', $personAge)
                            ->where(function ($q3) {
                                $q3->whereNull('job_order.age_min')
                                    ->orWhere('job_order.age_min', 0);
                            })
                            ->where('job_order.age_max', '>', 0);
                    })
                    // 4. age_min >= user age — yuqori yoshdagi ishlarni ko‘rsatish uchun
                    ->orWhere(function ($q2) use ($personAge) {
                        $q2->where('job_order.age_min', '>=', $personAge)
                            ->where('job_order.age_min', '>', 0);
                    })
                    // 🆕 5. age_min va age_max ikkalasi yo‘q (yoki 0) bo‘lsa — bu holat hamma yosh uchun ochiq
                    ->orWhere(function ($q2) {
                        $q2->where(function ($q3) {
                            $q3->whereNull('job_order.age_min')->orWhere('job_order.age_min', 0);
                        })->where(function ($q3) {
                            $q3->whereNull('job_order.age_max')->orWhere('job_order.age_max', 0);
                        });
                    });
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

        // if ($filters['salary']) {
        //     $filtered = $filtered->where('yearly_income_min', '>=', $filters['salary'] * 10000);
        // }

        if ($filters['location']) {
            $filtered = $filtered->filter(fn($job) => str_contains($job->prefecture_names, $filters['location']));
        }

        $this->jobs = $filtered;
        $this->updateCount = $filtered->count();
        Log::channel('job_search_log')->info('📡 getJobsProperty 実行', [
            'filters' => $this->filters,
            '最終件数' => $this->updateCount,
            'staff_code' => $staffCode
        ]);        
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
            'jobCategories' => $this->jobCategories,
        ]);
    }
}
