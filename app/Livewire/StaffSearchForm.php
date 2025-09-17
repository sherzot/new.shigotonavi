<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class StaffSearchForm extends Component
{
    // form fieldlar
    public $keyword = '';
    public $age = '';
    public $prefecture_code = '';
    public $prefecture_code2 = '';
    public $big_class_code = '';
    public $job_category = '';
    public $big_class_code2 = '';
    public $job_category2 = '';
    public $group_code = '';
    public $category_code = '';
    public $license_code = '';
    public $hope_salary = '';
    public $hope_job = '';
    public $hope_location = '';
    public $groups = [];
    public $categories = [];
    public $licenses = [];
    public $bigClasses = [];
    public $jobCategories = [];
    public $jobCategories2 = [];
    public $results = []; // 検索結果
    public $skills = [];

    public $skillCategories = [
        'OS' => 'OS',
        'Application' => 'アプリケーション',
        'Database' => 'データベース',
    ];

    public $allSkills = [];

    public function mount()
    {
        // 1. master_license
        $this->groups = DB::table('master_license')
            ->select('group_code', 'group_name')
            ->distinct()->get();

        // 2. master_job_type
        $this->bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()->get();

        // 3. skill categories ni master_code dan dynamic olish
        $this->allSkills = DB::table('master_code')
            ->whereIn('category_code', array_keys($this->skillCategories))
            ->select('category_code', 'code', 'detail')
            ->orderBy('detail')
            ->get()
            ->groupBy('category_code')
            ->toArray();
    }

    public function updatedGroupCode()
    {
        $this->category_code = '';
        $this->license_code = '';
        $this->categories = DB::table('master_license')
            ->select('category_code', 'category_name')
            ->where('group_code', $this->group_code)
            ->distinct()->get();

        $this->licenses = [];
    }

    public function updatedCategoryCode()
    {
        $this->license_code = '';
        $this->licenses = DB::table('master_license')
            ->select('code', 'name')
            ->where('group_code', $this->group_code)
            ->where('category_code', $this->category_code)
            ->get();
    }

    public function updatedBigClassCode()
    {
        $this->job_category = '';
        $this->jobCategories = DB::table('master_job_type')
            ->select('middle_class_code', 'middle_clas_name')
            ->where('big_class_code', $this->big_class_code)
            ->get();
    }
    public function updatedBigClassCode2()
    {
        $this->job_category2 = '';
        $this->jobCategories2 = DB::table('master_job_type')
            ->select('middle_class_code', 'middle_clas_name')
            ->where('big_class_code', $this->big_class_code2)
            ->get();
    }

    public function submitSearch()
    {
        $query = DB::table('master_person')
            ->leftJoin('person_hope_working_place', 'master_person.staff_code', '=', 'person_hope_working_place.staff_code')
            ->leftJoin('person_hope_working_condition', 'master_person.staff_code', '=', 'person_hope_working_condition.staff_code')
            ->select('master_person.*', 'person_hope_working_place.prefecture_code as hope_prefecture', 'person_hope_working_condition.yearly_income_min')
            ->whereNotNull('master_person.staff_code');

        if (!empty($this->keyword)) {
            $query->where(function ($q) {
                $q->where('master_person.staff_code', 'like', '%' . $this->keyword . '%')
                    ->orWhere('master_person.mail_address', 'like', '%' . $this->keyword . '%')
                    ->orWhere('master_person.name', 'like', '%' . $this->keyword . '%');
            });
        }

        if (!empty($this->age)) {
            $query->where('master_person.birthday', '<=', now()->subYears($this->age)->format('Y-m-d'));
        }

        if (!empty($this->prefecture_code)) {
            $query->where('master_person.prefecture_code', $this->prefecture_code);
        }

        if (!empty($this->prefecture_code2)) {
            $query->where('person_hope_working_place.prefecture_code', $this->prefecture_code2);
        }

        $staffList = $query->distinct()->get();
        $staffCodes = $staffList->pluck('staff_code')->toArray();

        $jobTypes = DB::table('person_hope_job_type')
            ->whereIn('staff_code', $staffCodes)
            ->pluck('job_type_detail', 'staff_code');

        $locations = DB::table('person_hope_working_place')
            ->join('master_code', 'person_hope_working_place.prefecture_code', '=', 'master_code.code')
            ->where('master_code.category_code', 'Prefecture')
            ->whereIn('person_hope_working_place.staff_code', $staffCodes)
            ->select('person_hope_working_place.staff_code', 'master_code.detail')
            ->get()
            ->groupBy('staff_code')
            ->map(fn($items) => $items->pluck('detail')->toArray());

        $licenses = DB::table('person_license')
            ->join('master_license', function ($join) {
                $join->on('person_license.group_code', '=', 'master_license.group_code')
                    ->on('person_license.category_code', '=', 'master_license.category_code')
                    ->on('person_license.code', '=', 'master_license.code');
            })
            ->whereIn('person_license.staff_code', $staffCodes)
            ->select('person_license.staff_code', 'master_license.name')
            ->get()
            ->groupBy('staff_code')
            ->map(fn($items) => $items->pluck('name')->toArray());

        $skills = DB::table('person_skill')
            ->join('master_code', 'person_skill.code', '=', 'master_code.code')
            ->where('master_code.category_code', 'SkillType')
            ->whereIn('person_skill.staff_code', $staffCodes)
            ->select('person_skill.staff_code', 'master_code.detail')
            ->get()
            ->groupBy('staff_code')
            ->map(fn($items) => $items->pluck('detail')->toArray());

        $results = collect();

        foreach ($staffList as $staff) {
            if (!empty($this->skills)) {
                foreach ($this->skills as $skillCode) {
                    if (!$skillCode) continue;
                    $matched = DB::table('person_skill')
                        ->where('staff_code', $staff->staff_code)
                        ->where('code', $skillCode)
                        ->exists();
                    if (!$matched) continue 2;
                }
            }

            $staff->jobType = $jobTypes[$staff->staff_code] ?? '';
            $staff->location = $locations[$staff->staff_code] ?? [];
            $staff->licenses = $licenses[$staff->staff_code] ?? [];
            $staff->skills = $skills[$staff->staff_code] ?? [];

            $results->push($staff);
        }

        $this->results = $results;
    }


    public function render()
    {
        return view('livewire.staff-search-form', [
            'results' => $this->results
        ]);
    }
}
