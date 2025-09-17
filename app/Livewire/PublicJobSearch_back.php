<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class PublicJobSearch extends Component
{
    use WithPagination;

    public $keyword = '';
    public $salary = '';
    public $hourly_wage = '';
    public $location = '';
    public $certificate = '';
    public $big_class_code = '';
    public $job_category = '';
    public $jobCategories = [];
    public $supplementFlags = [];

    public $initialResults = [];
    public $filteredResults = [];

    public function updated($property)
    {
        $this->resetPage();

        if (in_array($property, ['salary', 'location', 'certificate', 'supplementFlags'])) {
            $this->applyFilters();
        }
    }

    // public function updatedBigClassCode($value)
    // {
    //     $this->jobCategories = DB::table('master_job_type')
    //         ->where('big_class_code', $value)
    //         ->select('middle_class_code', 'middle_clas_name')
    //         ->get()
    //         ->toArray();

    //     $this->job_category = '';
    // }

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
            'appointment_flag' => '正社員登録',
            'license_acquisition_support_flag' => '資格取得支援あり'
        ];
    }

    public $hasSearched = false;
    public function searchJobs()
    {
        $this->hasSearched = true;
        $middleClassMap = DB::table('master_job_type')
            ->pluck('middle_clas_name', DB::raw("CONCAT(big_class_code, middle_class_code, '000')"))
            ->toArray();

        $query = DB::table('job_order')
            ->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
            ->join('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
            ->join('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
            ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
            ->join('master_code', function ($join) {
                $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                    ->where('master_code.category_code', '=', 'Prefecture');
            })
            ->where('job_order.public_flag', 1)
            ->where('job_order.order_progress_type', 1)
            ->where('job_order.public_limit_day', '>=', now());

        if ($this->keyword) {
            $query->where('job_order.job_type_detail', 'like', "%{$this->keyword}%");
        }

        $code = null;

        Log::info('Selected big_class_code: ' . $this->big_class_code);
        Log::info('Selected job_category: ' . $this->job_category);

        if ($this->big_class_code && $this->job_category) {
            $code = $this->big_class_code . $this->job_category . '000';
            Log::info("Strictly filtering with job_type_code = $code");
            $query->where('job_job_type.job_type_code', $code);
        } elseif ($this->big_class_code) {
            Log::info("Filtering all types under big_class_code = {$this->big_class_code}");
            $query->where('job_job_type.job_type_code', 'like', $this->big_class_code . '%');
        }

        if ($this->salary) {
            $salaryYen = $this->salary * 10000;
            $query->where(function ($q) use ($salaryYen) {
                $q->where(function ($q1) use ($salaryYen) {
                    $q1->where('job_order.yearly_income_min', '>', 0)
                       ->where('job_order.yearly_income_min', '>=', $salaryYen);
                })->orWhere(function ($q2) {
                    $q2->where('job_order.yearly_income_min', '=', 0)
                       ->where('job_order.hourly_income_min', '>=', $this->salary);
                });
            });
        } elseif ($this->hourly_wage) {
            $query->where('job_order.hourly_income_min', '>=', $this->hourly_wage);
        }
        

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
            'master_code.detail as prefecture_name',
            'job_job_type.job_type_code',
            'job_order.created_at',
            'job_order.order_type',
            'job_order.business_detail',
            // 'master_company.foundation_year',
            // 'master_company.employee_number',
        ])
            ->addSelect(array_keys($this->checkboxOptions()))
            ->orderByDesc('job_order.update_at')
            ->get();

        // ✅ 補足フラグ配列が追加されました
        // foreach ($results as $job) {
        //     $job->selectedFlagsArray = collect($this->checkboxOptions())
        //         ->filter(fn($_, $key) => !empty($job->$key) && $job->$key == 1)
        //         ->values()
        //         ->toArray();
        // }
        foreach ($results as $job) {
            // 🔹 Supplement flags array
            $job->selectedFlagsArray = collect($this->checkboxOptions())
                ->filter(fn($_, $key) => !empty($job->$key) && $job->$key == 1)
                ->values()
                ->toArray();

            // 🔹 Employment label
            $job->employment_label = match ($job->order_type) {
                1 => '派遣',
                2 => '正社員',
                3 => '契約社員',
                default => '不明',
            };
        }


        $this->initialResults = $results;
        $this->applyFilters();
        Log::info('Searching with job_type_code: ' . ($code ?? 'not set'));
        Log::info('Results:', $results->pluck('job_type_code')->toArray());
    }


    public function applyFilters()
    {
        $filtered = collect($this->initialResults);
        if ($this->salary) {
            $filtered = $filtered->filter(function ($job) {
                $salaryYen = $this->salary * 10000;

                if ($job->yearly_income_min > 0) {
                    return $job->yearly_income_min >= $salaryYen;
                }
                return false;
            });
        }
        if ($this->location) {
            $filtered = $filtered->filter(fn($job) => $job->prefecture_name == $this->location);
        }

        if ($this->certificate) {
            $filtered = $filtered->filter(fn($job) => str_contains($job->job_type_detail, $this->certificate));
        }

        foreach ($this->supplementFlags as $flag) {
            $filtered = $filtered->filter(fn($job) => !empty($job->$flag) && $job->$flag == 1);
        }
        if ($this->hourly_wage) {
            $filtered = $filtered->filter(fn($job) => $job->hourly_income_min >= $this->hourly_wage);
        }
        $this->filteredResults = $filtered->values();
    }


    public function render()
    {
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

            'jobs' => $this->filteredResults,
            'middleClassMap' => $middleClassMap,
            'checkboxOptions' => $this->checkboxOptions(),

        ]);
    }
}
