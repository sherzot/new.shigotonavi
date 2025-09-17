<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResumeInfo extends Component
{
    public $name, $name_f, $mail_address, $birthday, $sex;
    public $prefecture_code, $post_u, $post_l, $address, $address_f;
    public $city, $city_f, $town, $town_f, $portable_telephone_number;
    public $educations = [];
    public $careers = [];
    public $jobTypes = [];
    public $showJobSearchForm = false;

    public function mount()
    {
        $staffCode = Auth::user()->staff_code;
        $person = DB::table('master_person')->where('staff_code', $staffCode)->first();

        if ($person) {
            $this->name = $person->name;
            $this->name_f = $person->name_f;
            $this->mail_address = $person->mail_address;
            $this->birthday = $person->birthday ? Carbon::parse($person->birthday)->format('Ymd') : '';
            $this->sex = $person->sex;
            $this->prefecture_code = $person->prefecture_code;
            $this->post_u = $person->post_u;
            $this->post_l = $person->post_l;
            $this->address = $person->address;
            $this->address_f = $person->address_f;
            $this->city = $person->city;
            $this->city_f = $person->city_f;
            $this->town = $person->town;
            $this->town_f = $person->town_f;
            $this->portable_telephone_number = $person->portable_telephone_number;
        }

        $this->educations = DB::table('person_educate_history')
            ->where('staff_code', $staffCode)
            ->get()
            ->map(function ($edu) {
                return [
                    'school_name' => $edu->school_name,
                    'school_type_code' => $edu->school_type_code,
                    'entry_day_year' => Carbon::parse($edu->entry_day)->format('Y'),
                    'entry_day_month' => Carbon::parse($edu->entry_day)->format('m'),
                    'graduate_day_year' => Carbon::parse($edu->graduate_day)->format('Y'),
                    'graduate_day_month' => Carbon::parse($edu->graduate_day)->format('m'),
                    'speciality' => $edu->speciality,
                    'course_type' => $edu->course_type,
                    'entry_type_code' => $edu->entry_type_code,
                    'graduate_type_code' => $edu->graduate_type_code,
                ];
            })->toArray();

        $this->careers = DB::table('person_career_history as pch')
            ->leftJoin('master_job_type as mj', 'pch.job_type_code', '=', 'mj.all_connect_code')
            ->where('pch.staff_code', $staffCode)
            ->get()
            ->map(function ($career) {
                return [
                    'company_name' => $career->company_name,
                    'capital' => intval($career->capital / 10000),
                    'number_employees' => $career->number_employees,
                    'entry_day_year' => Carbon::parse($career->entry_day)->format('Y'),
                    'entry_day_month' => Carbon::parse($career->entry_day)->format('m'),
                    'retire_day_year' => Carbon::parse($career->retire_day)->format('Y'),
                    'retire_day_month' => Carbon::parse($career->retire_day)->format('m'),
                    'industry_type_code' => $career->industry_type_code,
                    'working_type_code' => $career->working_type_code,
                    'job_type_detail' => $career->job_type_detail,
                    'job_type_big_code' => $career->big_class_code,
                    'job_type_small_code' => $career->middle_class_code,
                    'business_detail' => $career->business_detail,
                ];
            })->toArray();

        $this->jobTypes = DB::table('master_job_type')->whereNotIn('middle_class_code', ['00'])->get();
    }

    public function getSmallJobTypes($index)
    {
        $bigClassCode = $this->careers[$index]['job_type_big_code'] ?? null;
        return $bigClassCode ? $this->jobTypes->where('big_class_code', $bigClassCode) : collect();
    }

    public function addEducationRow() { $this->educations[] = []; }
    public function removeEducationRow($index) { unset($this->educations[$index]); $this->educations = array_values($this->educations); }
    public function addCareerRow() { $this->careers[] = []; }
    public function removeCareerRow($index) { unset($this->careers[$index]); $this->careers = array_values($this->careers); }

    public function save()
    {
        $staffCode = Auth::user()->staff_code;

        $this->validate([
            'name' => 'required|string|max:255',
            'name_f' => 'required|string|max:255',
            'mail_address' => 'required|email',
            'birthday' => 'required|digits:8',
            'sex' => 'required|in:1,2',
            'post_u' => 'required|size:3',
            'post_l' => 'required|size:4',
            'prefecture_code' => 'required|string',
            'portable_telephone_number' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            DB::table('master_person')->where('staff_code', $staffCode)->update([
                'name' => $this->name,
                'name_f' => $this->name_f,
                'mail_address' => $this->mail_address,
                'birthday' => Carbon::createFromFormat('Ymd', $this->birthday)->format('Y-m-d'),
                'sex' => $this->sex,
                'prefecture_code' => $this->prefecture_code,
                'post_u' => $this->post_u,
                'post_l' => $this->post_l,
                'address' => $this->address,
                'address_f' => $this->address_f,
                'city' => $this->city,
                'city_f' => $this->city_f,
                'town' => $this->town,
                'town_f' => $this->town_f,
                'portable_telephone_number' => $this->portable_telephone_number,
                'updated_at' => now(),
            ]);

            DB::table('person_educate_history')->where('staff_code', $staffCode)->delete();
            foreach ($this->educations as $index => $edu) {
                DB::table('person_educate_history')->insert([
                    'staff_code' => $staffCode,
                    'id' => $index + 1,
                    'school_name' => $edu['school_name'] ?? '',
                    'school_type_code' => $edu['school_type_code'] ?? '',
                    'entry_day' => $edu['entry_day_year'] . '-' . $edu['entry_day_month'] . '-01',
                    'graduate_day' => $edu['graduate_day_year'] . '-' . $edu['graduate_day_month'] . '-01',
                    'speciality' => $edu['speciality'] ?? '',
                    'course_type' => $edu['course_type'] ?? '',
                    'entry_type_code' => $edu['entry_type_code'] ?? '',
                    'graduate_type_code' => $edu['graduate_type_code'] ?? '',
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
            }

            DB::table('person_career_history')->where('staff_code', $staffCode)->delete();
            foreach ($this->careers as $index => $career) {
                $jobTypeCode = str_pad($career['job_type_big_code'], 2, '0', STR_PAD_LEFT)
                    . str_pad($career['job_type_small_code'], 2, '0', STR_PAD_LEFT)
                    . '000';

                DB::table('person_career_history')->insert([
                    'staff_code' => $staffCode,
                    'id' => $index + 1,
                    'company_name' => $career['company_name'] ?? '',
                    'capital' => intval($career['capital']) * 10000,
                    'number_employees' => $career['number_employees'] ?? 0,
                    'entry_day' => $career['entry_day_year'] . '-' . $career['entry_day_month'] . '-01',
                    'retire_day' => $career['retire_day_year'] . '-' . $career['retire_day_month'] . '-01',
                    'industry_type_code' => $career['industry_type_code'] ?? '',
                    'working_type_code' => $career['working_type_code'] ?? '',
                    'job_type_code' => $jobTypeCode,
                    'job_type_detail' => $career['job_type_detail'] ?? '',
                    'business_detail' => $career['business_detail'] ?? '',
                    'created_atday' => now(),
                    'update_at' => now(),
                ]);
            }

            DB::commit();
            $this->showJobSearchForm = true;
            $this->dispatch('saved', ['message' => '保存に成功しました。']);
            session()->flash('show_job_search', true);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => $e->getMessage(),
                'trace' => app()->environment('local') ? $e->getTraceAsString() : null,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.resume-info', [
            'schoolTypes' => DB::table('master_code')->where('category_code', 'SchoolType')->get(),
            'courseTypes' => DB::table('master_code')->where('category_code', 'CourseType')->get(),
            'entryTypes' => DB::table('master_code')->where('category_code', 'EntryType')->get(),
            'graduateTypes' => DB::table('master_code')->where('category_code', 'GraduateType')->get(),
            'industryTypes' => DB::table('master_code')->where('category_code', 'IndustryTypeDsp')->get(),
            'workingTypes' => DB::table('master_code')->where('category_code', 'WorkingType')->get(),
            'jobTypes' => $this->jobTypes,
            'prefectures' => DB::table('master_code')->where('category_code', 'Prefecture')->get(),
            'showJobSearchForm' => $this->showJobSearchForm,
        ]);
    }
}


// livewire.resume-info