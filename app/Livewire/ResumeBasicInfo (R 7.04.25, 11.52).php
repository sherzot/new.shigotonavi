<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResumeBasicInfo extends Component
{
    use WithFileUploads;

    public $activeAccordion = 'basic';
    public $name, $name_f, $mail_address;
    public $birthday;
    public $prefecture_code, $post_u, $post_l, $address, $address_f, $city, $city_f, $town, $town_f;
    public $sex;
    public $portable_telephone_number;
    public $photo;
    public $existingPhoto;
    public $educations = [];
    public $careers = [];

    public function switchAccordion($section)
    {
        $this->activeAccordion = $section;
    }

    public function addEducationRow()
    {
        $this->educations[] = [
            'id' => null,
            'school_name' => '',
            'school_type_code' => '',
            'entry_day_year' => '',
            'entry_day_month' => '',
            'graduate_day_year' => '',
            'graduate_day_month' => '',
        ];
    }

    public function removeEducationRow($index)
    {
        unset($this->educations[$index]);
        $this->educations = array_values($this->educations);
    }

    public function addCareerRow()
    {
        $this->careers[] = [
            'id' => null,
            'company_name' => '',
            'capital' => '',
            'number_employees' => '',
            'entry_day_year' => '',
            'entry_day_month' => '',
            'retire_day_year' => '',
            'retire_day_month' => '',
            'industry_type_code' => '',
            'working_type_code' => '',
            'job_type_detail' => '',
            'job_type_big_code' => '',
            'job_type_small_code' => '',
            'business_detail' => '',
        ];
    }

    public function removeCareerRow($index)
    {
        unset($this->careers[$index]);
        $this->careers = array_values($this->careers);
    }

    public function mount()
    {
        $staffCode = Auth::user()->staff_code;
        $person = DB::table('master_person')->where('staff_code', $staffCode)->first();

        if ($person) {
            $this->name = $person->name;
            $this->name_f = $person->name_f;
            $this->mail_address = $person->mail_address;
            $this->sex = $person->sex;
            $this->birthday = $person->birthday ? Carbon::parse($person->birthday)->format('Ymd') : '';
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

            $photoRecord = DB::table('person_picture')->where('staff_code', $staffCode)->first();
            if ($photoRecord && !empty($photoRecord->picture)) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($finfo, $photoRecord->picture);
                finfo_close($finfo);
                $this->existingPhoto = 'data:' . $mime . ';base64,' . base64_encode($photoRecord->picture);
            }
        }

        $this->educations = DB::table('person_educate_history')
            ->where('staff_code', $staffCode)
            ->get()
            ->map(function ($edu) {
                return [
                    'id' => $edu->id,
                    'school_name' => $edu->school_name,
                    'school_type_code' => $edu->school_type_code,
                    'entry_day_year' => Carbon::parse($edu->entry_day)->format('Y'),
                    'entry_day_month' => Carbon::parse($edu->entry_day)->format('m'),
                    'graduate_day_year' => Carbon::parse($edu->graduate_day)->format('Y'),
                    'graduate_day_month' => Carbon::parse($edu->graduate_day)->format('m'),
                ];
            })->toArray();

        $this->careers = DB::table('person_career_history')
            ->where('staff_code', $staffCode)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'company_name' => $job->company_name,
                    'capital' => $job->capital,
                    'number_employees' => $job->number_employees,
                    'entry_day_year' => Carbon::parse($job->entry_day)->format('Y'),
                    'entry_day_month' => Carbon::parse($job->entry_day)->format('m'),
                    'retire_day_year' => Carbon::parse($job->retire_day)->format('Y'),
                    'retire_day_month' => Carbon::parse($job->retire_day)->format('m'),
                    'job_type_detail' => $job->job_type_detail,
                ];
            })->toArray();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'name_f' => 'required|string|max:255',
            'mail_address' => 'required|email|unique:master_person,mail_address,' . Auth::user()->staff_code . ',staff_code',
            'birthday' => 'required|digits:8',
            'prefecture_code' => 'required|string',
            'post_u' => 'required|size:3',
            'post_l' => 'required|size:4',
            'sex' => 'required|in:1,2',
            'portable_telephone_number' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:500'
        ]);

        $staffCode = Auth::user()->staff_code;

        if ($this->photo) {
            $imageData = file_get_contents($this->photo->getRealPath());
            DB::table('person_picture')->updateOrInsert(
                ['staff_code' => $staffCode],
                [
                    'picture' => $imageData,
                    'created_at' => now(),
                    'update_at' => now(),
                ]
            );

            $mime = $this->photo->getMimeType();
            $this->existingPhoto = 'data:' . $mime . ';base64,' . base64_encode($imageData);
        }

        DB::table('master_person')->updateOrInsert(
            ['staff_code' => $staffCode],
            [
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
                'update_at' => now()
            ]
        );

        foreach ($this->educations as $edu) {
            $id = $edu['id'] ?? null;
            if (!$id) {
                $id = (DB::table('person_educate_history')->where('staff_code', $staffCode)->max('id') ?? 0) + 1;
            }
            DB::table('person_educate_history')->updateOrInsert(
                ['staff_code' => $staffCode, 'id' => $id],
                [
                    'school_name' => $edu['school_name'],
                    'school_type_code' => $edu['school_type_code'],
                    'entry_day' => $edu['entry_day_year'] . '-' . $edu['entry_day_month'] . '-01',
                    'graduate_day' => $edu['graduate_day_year'] . '-' . $edu['graduate_day_month'] . '-01',
                    'update_at' => now()
                ]
            );
        }

        foreach ($this->careers as $job) {
            $id = $job['id'] ?? null;
            if (!$id) {
                $id = (DB::table('person_career_history')->where('staff_code', $staffCode)->max('id') ?? 0) + 1;
            }
            DB::table('person_career_history')->updateOrInsert(
                ['staff_code' => $staffCode, 'id' => $id],
                [
                    'company_name' => $job['company_name'],
                    'capital' => $job['capital'],
                    'number_employees' => $job['number_employees'],
                    'entry_day' => $job['entry_day_year'] . '-' . $job['entry_day_month'] . '-01',
                    'retire_day' => $job['retire_day_year'] . '-' . $job['retire_day_month'] . '-01',
                    'job_type_detail' => $job['job_type_detail'],
                    'update_at' => now()
                ]
            );
        }

        session()->flash('success', '基本情報・学歴・職歴が保存されました。');
    }

    public function render()
    {
        $prefectures = DB::table('master_code')->where('category_code', 'Prefecture')->get();
        $schoolTypes = DB::table('master_code')->where('category_code', 'SchoolType')->get();
        $courseTypes = DB::table('master_code')->where('category_code', 'CourseType')->get();
        $entryTypes = DB::table('master_code')->where('category_code', 'EntryType')->get();
        $graduateTypes = DB::table('master_code')->where('category_code', 'GraduateType')->get();
        $industryTypes = DB::table('master_code')->where('category_code', 'IndustryTypeDsp')->get();
        $workingTypes = DB::table('master_code')->where('category_code', 'WorkingType')->get();
        $jobTypes = DB::table('master_job_type')
            ->whereNotIn('middle_class_code',  ['00'])
            ->get();

        return view('livewire.resume-basic-info', [
            'prefectures' => $prefectures,
            'schoolTypes' => $schoolTypes,
            'courseTypes' => $courseTypes,
            'entryTypes' => $entryTypes,
            'graduateTypes' => $graduateTypes,
            'industryTypes' => $industryTypes,
            'workingTypes' => $workingTypes,
            'jobTypes' => $jobTypes,
        ]);
    }
}
