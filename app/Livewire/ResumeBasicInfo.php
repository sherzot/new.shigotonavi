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
    public $licenses = [];
    public $licenseGroups = [];       // グループ
    public $licenseCategories = [];   // カテゴリ
    public $licenseNames = [];        // 資格名
    public $self_pr = '';
    public $skills = []; // 選択されたスキルコード（カテゴリ別）
    public $skillCategories = [];
    public $allSkills = [];
    public $resumePreference = [
        'subject' => '',
        'commute_time' => '',
        'wish_motive' => '',
        'hope_column' => '',
    ];
    public $hopeCondition = [
        'job_type_big_code' => '',
        'job_type_small_code' => '',
        'prefecture_code' => '',
        'salary_type' => '',
        'yearly_income_min' => '',
        'hourly_income_min' => '',
    ];

    public $jobTypes = [];

    public $marriage_flag, $dependent_number, $dependent_flag;


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
    public function getSmallJobTypes($index)
    {
        $bigClassCode = $this->careers[$index]['job_type_big_code'] ?? null;

        if (!$bigClassCode) {
            return collect(); // Bo‘sh kolleksiya qaytaradi
        }

        return $this->jobTypes->where('big_class_code', $bigClassCode);
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
    public function addLicenseRow()
    {
        $this->licenses[] = [
            'id' => null,
            'group_code' => '',
            'category_code' => '',
            'code' => '',
            'get_day' => '',
        ];
    }

    public function removeLicenseRow($index)
    {
        unset($this->licenses[$index]);
        $this->licenses = array_values($this->licenses);
    }
    public function getHopeSmallJobTypes()
    {
        if (!$this->hopeCondition['job_type_big_code']) {
            return collect();
        }

        return $this->jobTypes->filter(function ($type) {
            return $type->big_class_code === $this->hopeCondition['job_type_big_code'];
        });
    }

    public function mount()
    {
        $staffCode = Auth::user()->staff_code;

        // 🔹 基本情報 (Personal info)
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
        }

        // 🔹 写真
        $photoRecord = DB::table('person_picture')->where('staff_code', $staffCode)->first();
        if ($photoRecord && $photoRecord->picture) {
            $mime = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $photoRecord->picture);
            $this->existingPhoto = 'data:' . $mime . ';base64,' . base64_encode($photoRecord->picture);
        }

        // 🔹 学歴情報 (Education)
        $this->educations = DB::table('person_educate_history as edu')
            ->select(
                'edu.id',
                'edu.school_name',
                'edu.school_type_code',
                DB::raw('YEAR(edu.entry_day) as entry_day_year'),
                DB::raw('LPAD(MONTH(edu.entry_day), 2, "0") as entry_day_month'),
                DB::raw('YEAR(edu.graduate_day) as graduate_day_year'),
                DB::raw('LPAD(MONTH(edu.graduate_day), 2, "0") as graduate_day_month'),
                'edu.speciality',
                'edu.course_type',
                'edu.entry_type_code',
                'edu.graduate_type_code'
            )
            ->where('edu.staff_code', $staffCode)
            ->get()
            ->map(function ($edu) {
                return [
                    'id' => $edu->id,
                    'school_name' => $edu->school_name,
                    'school_type_code' => $edu->school_type_code,
                    'entry_day_year' => $edu->entry_day_year,
                    'entry_day_month' => $edu->entry_day_month,
                    'graduate_day_year' => $edu->graduate_day_year,
                    'graduate_day_month' => $edu->graduate_day_month,
                    'speciality' => $edu->speciality,
                    'course_type' => $edu->course_type,
                    'entry_type_code' => $edu->entry_type_code,
                    'graduate_type_code' => $edu->graduate_type_code,
                ];
            })->toArray();

        // 🔹 職歴情報 (Career)
        $this->careers = DB::table('person_career_history as pch')
            ->leftJoin('master_job_type as mj', 'pch.job_type_code', '=', 'mj.all_connect_code')
            ->select(
                'pch.id',
                'pch.company_name',
                'pch.capital',
                'pch.number_employees',
                DB::raw('YEAR(pch.entry_day) as entry_day_year'),
                DB::raw('LPAD(MONTH(pch.entry_day), 2, "0") as entry_day_month'),
                DB::raw('YEAR(pch.retire_day) as retire_day_year'),
                DB::raw('LPAD(MONTH(pch.retire_day), 2, "0") as retire_day_month'),
                'pch.industry_type_code',
                'pch.working_type_code',
                'pch.job_type_detail',
                'mj.big_class_code',
                'mj.middle_class_code',
                'pch.business_detail'
            )
            ->where('pch.staff_code', $staffCode)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'company_name' => $job->company_name,
                    'capital' => intval($job->capital / 10000),
                    'number_employees' => $job->number_employees,
                    'entry_day_year' => $job->entry_day_year,
                    'entry_day_month' => $job->entry_day_month,
                    'retire_day_year' => $job->retire_day_year,
                    'retire_day_month' => $job->retire_day_month,
                    'industry_type_code' => $job->industry_type_code,
                    'working_type_code' => $job->working_type_code,
                    'job_type_detail' => $job->job_type_detail,
                    'job_type_big_code' => $job->big_class_code ?? '',
                    'job_type_small_code' => $job->middle_class_code ?? '',
                    'business_detail' => $job->business_detail,
                ];
            })->toArray();

        $this->licenseGroups = DB::table('master_license')
            ->select('group_code', 'group_name')
            ->distinct()
            ->get();

        $this->licenseCategories = DB::table('master_license')
            ->select('group_code', 'category_code', 'category_name')
            ->distinct()
            ->get()
            ->groupBy(function ($item) {
                return $item->group_code;
            });

        $this->licenseNames = DB::table('master_license')
            ->select('group_code', 'category_code', 'code', 'name')
            ->distinct()
            ->get()
            ->groupBy(function ($item) {
                return $item->group_code . '_' . $item->category_code;
            });

        // 🔹 登録済み資格
        $this->licenses = DB::table('person_license as pl')
            ->leftJoin('master_license as ml', function ($join) {
                $join->on('pl.group_code', '=', 'ml.group_code')
                    ->on('pl.category_code', '=', 'ml.category_code')
                    ->on('pl.code', '=', 'ml.code');
            })
            ->where('pl.staff_code', $staffCode)
            ->select(
                'pl.id',
                'pl.group_code',
                'pl.category_code',
                'pl.code',
                'pl.get_day',
                'ml.category_name',
                'ml.name as license_name'
            )
            ->orderBy('pl.id')
            ->get()
            ->map(function ($license) {
                return [
                    'id' => $license->id,
                    'group_code' => $license->group_code,
                    'category_code' => $license->category_code,
                    'code' => $license->code,
                    'get_day' => $license->get_day ? Carbon::parse($license->get_day)->format('Ymd') : '',
                ];
            })->toArray();
        // 🔹 自己PR 初期値
        $self = DB::table('person_self_pr')->where('staff_code', $staffCode)->first();
        $this->self_pr = $self->self_pr ?? '';

        $this->skillCategories = DB::table('master_code')
            ->whereIn('category_code', ['OS', 'Application', 'DevelopmentLanguage', 'Database'])
            ->select('category_code', 'category_code as label') // ラベルとコードを同じにしておく（任意でカスタム）
            ->pluck('label', 'category_code')
            ->toArray();

        $this->allSkills = DB::table('master_code')
            ->whereIn('category_code', array_keys($this->skillCategories))
            ->select('category_code', 'code', 'detail')
            ->get()
            ->groupBy('category_code')
            ->toArray();

        $selected = DB::table('person_skill')
            ->where('staff_code', $staffCode)
            ->get();
        // 🔹 💥 jobTypes をここで読み込む
        $this->jobTypes = DB::table('master_job_type')
            ->whereNotIn('middle_class_code', ['00'])
            ->get();
        foreach ($selected as $skill) {
            $this->skills[$skill->category_code] = $skill->code;
        }
        $other = DB::table('person_resume_other')->where('staff_code', $staffCode)->first();
        if ($other) {
            $this->resumePreference['subject'] = $other->subject;
            $this->resumePreference['commute_time'] = $other->commute_time;
            $this->resumePreference['wish_motive'] = $other->wish_motive;
            $this->resumePreference['hope_column'] = $other->hope_column;
        }
        // 🔹 希望職種（大分類・小分類）
        $hopeJob = DB::table('person_hope_job_type')->where('staff_code', $staffCode)->first();
        if ($hopeJob && $hopeJob->job_type_code) {
            $big = substr($hopeJob->job_type_code, 0, 2);     // 例: 03
            $middle = substr($hopeJob->job_type_code, 2, 2);  // 例: 01

            $this->hopeCondition['job_type_big_code'] = $big;
            $this->hopeCondition['job_type_small_code'] = $middle;
        }

        // 🔹 希望勤務地（都道府県）
        $hopePlace = DB::table('person_hope_working_place')->where('staff_code', $staffCode)->first();
        if ($hopePlace) {
            $this->hopeCondition['prefecture_code'] = $hopePlace->prefecture_code;
        }

        // 🔹 希望給料（salary列に格納）
        $hopeSalary = DB::table('person_hope_working_condition')->where('staff_code', $staffCode)->first();
        if ($hopeSalary) {
            // 年収希望
            if ($hopeSalary->yearly_income_min > 0) {
                $this->hopeCondition['salary_type'] = 'yearly';
                $this->hopeCondition['yearly_income_min'] = intval($hopeSalary->yearly_income_min / 10000); // ← 🔥 bo‘lish
            }
            
            // 時給希望
            if ($hopeSalary->hourly_income_min > 0) {
                $this->hopeCondition['salary_type'] = 'hourly';
                $this->hopeCondition['hourly_income_min'] = $hopeSalary->hourly_income_min;
            }
        }

        $this->marriage_flag = $person->marriage_flag;
        $this->dependent_number = $person->dependent_number;
        $this->dependent_flag = $person->dependent_flag;
    }

    public function updatedLicenses($value, $name)
    {
        [$index, $field] = explode('.', $name);

        if ($field === 'group_code') {
            $this->licenses[$index]['category_code'] = '';
            $this->licenses[$index]['code'] = '';
        }

        if ($field === 'category_code') {
            $this->licenses[$index]['code'] = '';
        }
    }
    public function save()
    {
        $staffCode = Auth::user()->staff_code;

        // 1. Validate inputs
        $this->validate([
            'name' => 'nullable|string|max:255',
            'name_f' => 'nullable|string|max:255',
            'mail_address' => 'nullable|email',
            'birthday' => 'nullable|digits:8',
            'sex' => 'nullable|in:1,2',
            'post_u' => 'nullable|size:3',
            'post_l' => 'nullable|size:4',
            'prefecture_code' => 'nullable|string',
            'portable_telephone_number' => 'nullable|string',
            'address' => 'nullable|string',
            'address_f' => 'nullable|string',
            'city' => 'nullable|string',
            'city_f' => 'nullable|string',
            'town' => 'nullable|string',
            'town_f' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:500',
            'self_pr' => 'nullable|string|max:224',
            'resumePreference.wish_motive' => 'nullable|string|max:224',
            'resumePreference.hope_column' => 'nullable|string|max:224',
            'resumePreference.subject' => 'nullable|string|max:224',
        ], [], [
            // 📌 カスタムメッセージ (日本語の明らかな誤り)
            'name.required' => '氏名を入力してください。',
            'name.string' => '氏名は文字列で入力してください。',
            'name.max' => '氏名は255文字以内で入力してください。',

            'name_f.required' => '氏名（フリガナ）を入力してください。',
            'name_f.string' => '氏名（フリガナ）は文字列で入力してください。',
            'name_f.max' => '氏名（フリガナ）は255文字以内で入力してください。',

            'mail_address.required' => 'メールアドレスを入力してください。',
            'mail_address.email' => '正しいメールアドレス形式で入力してください。',

            'birthday.required' => '生年月日を入力してください。',
            'birthday.digits' => '生年月日は8桁（例：19900101）で入力してください。',

            'sex.required' => '性別を選択してください。',
            'sex.in' => '性別は「男性」または「女性」から選択してください。',

            'post_u.required' => '郵便番号（上3桁）を入力してください。',
            'post_u.size' => '郵便番号（上3桁）はちょうど3桁で入力してください。',

            'post_l.required' => '郵便番号（下4桁）を入力してください。',
            'post_l.size' => '郵便番号（下4桁）はちょうど4桁で入力してください。',

            'prefecture_code.required' => '都道府県を選択してください。',

            'portable_telephone_number.required' => '携帯電話番号を入力してください。',

            'address.string' => '住所（番地）は文字列で入力してください。',
            'address_f.string' => '住所（番地フリガナ）は文字列で入力してください。',
            'city.string' => '市区町村は文字列で入力してください。',
            'city_f.string' => '市区町村（フリガナ）は文字列で入力してください。',
            'town.string' => '町名・番地は文字列で入力してください。',
            'town_f.string' => '町名・番地（フリガナ）は文字列で入力してください。',

            'photo.image' => 'アップロードするファイルは画像でなければなりません。',
            'photo.mimes' => 'アップロードできる画像形式はjpeg、jpg、pngのみです。',
            'photo.max' => '画像ファイルのサイズは500KB以内にしてください。',

            'self_pr.string' => '自己PRは文字列で入力してください。',
            'self_pr.max' => '自己PRは200文字以内で入力してください。',

            'resumePreference.wish_motive.string' => '志望動機は文字列で入力してください。',
            'resumePreference.wish_motive.max' => '志望動機は200文字以内で入力してください。',
            'resumePreference.hope_column.string' => '本人希望欄は文字列で入力してください。',
            'resumePreference.hope_column.max' => '本人希望欄は200文字以内で入力してください。',
            'resumePreference.subject.string' => '志望動機の識別名は文字列で入力してください。',
            'resumePreference.subject.max' => '志望動機の識別名は200文字以内で入力してください。',
        ]);
        // 検証されたデータを記録します。
        Log::info('✅ 検証後のデータ:', [
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
            'marriage_flag' => $this->marriage_flag,
            'dependent_number' => $this->dependent_number,
            'dependent_flag' => $this->dependent_flag,
        ]);

        DB::beginTransaction();
        try {
            // 2. Photo upload
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
            }

            Log::info('🔹 master_person 更新開始');

            // 3. master_person update
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
                'marriage_flag' => $this->marriage_flag,
                'dependent_number' => $this->dependent_number,
                'dependent_flag' => $this->dependent_flag,
                'updated_at' => now(),
            ]);

            Log::info('✅ master_person 更新成功');
            // 4. 学歴情報 (education)
            DB::table('person_educate_history')->where('staff_code', $staffCode)->delete();
            foreach ($this->educations as $index => $edu) {
                DB::table('person_educate_history')->insert([
                    'staff_code' => $staffCode,
                    'id' => $index + 1,
                    'school_name' => $edu['school_name'],
                    'school_type_code' => $edu['school_type_code'],
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
            Log::info('🔹 person_career_history 保存開始');

            // 5. 職歴情報 (career)
            DB::table('person_career_history')->where('staff_code', $staffCode)->delete();
            foreach ($this->careers as $index => $career) {
                $jobTypeBig = str_pad($career['job_type_big_code'], 2, '0', STR_PAD_LEFT);
                $jobTypeSmall = str_pad($career['job_type_small_code'], 2, '0', STR_PAD_LEFT);
                $jobTypeCode = $jobTypeBig . $jobTypeSmall . '000'; // ❗ 結合 (例: 0301000)

                DB::table('person_career_history')->insert([
                    'staff_code' => $staffCode,
                    'id' => $index + 1,
                    'company_name' => $career['company_name'],
                    'capital' => intval($career['capital']) * 10000,
                    'number_employees' => $career['number_employees'],
                    'entry_day' => $career['entry_day_year'] . '-' . $career['entry_day_month'] . '-01',
                    'retire_day' => $career['retire_day_year'] . '-' . $career['retire_day_month'] . '-01',
                    'industry_type_code' => $career['industry_type_code'],
                    'working_type_code' => $career['working_type_code'],
                    'job_type_code' => $jobTypeCode, // ✅ ここに生成したコードを保存する
                    'job_type_detail' => $career['job_type_detail'],
                    'business_detail' => $career['business_detail'],
                    'created_atday' => now(),
                    'update_at' => now(),
                ]);
            }
            Log::info('✅ person_career_history 保存成功');

            // 6. 資格情報 (licenses)
            DB::table('person_license')->where('staff_code', $staffCode)->delete();
            foreach ($this->licenses as $index => $license) {
                if (!empty($license['group_code']) && !empty($license['category_code']) && !empty($license['code'])) {

                    // master_license から資格名を取得する
                    $licenseName = DB::table('master_license')
                        ->where('group_code', $license['group_code'])
                        ->where('category_code', $license['category_code'])
                        ->where('code', $license['code'])
                        ->value('name') ?? '';

                    DB::table('person_license')->insert([
                        'staff_code' => $staffCode,
                        'id' => $index + 1,
                        'group_code' => $license['group_code'],
                        'category_code' => $license['category_code'],
                        'code' => $license['code'],
                        'get_day' => !empty($license['get_day']) ? Carbon::createFromFormat('Ymd', $license['get_day'])->format('Y-m-d') : null,
                        'remark' => $licenseName,
                        'created_at' => now(),
                        'update_at' => now(),
                    ]);
                }
            }
            Log::info('✅ person_license 保存成功');
            // 7. 自己PR
            DB::table('person_self_pr')->where('staff_code', $staffCode)->delete();
            DB::table('person_self_pr')->insert([
                'staff_code' => $staffCode,
                'self_pr' => $this->self_pr,
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info('✅ person_self_pr 保存成功');
            // 8. スキル
            DB::table('person_skill')->where('staff_code', $staffCode)->delete();
            $i = 1;
            foreach ($this->skills as $category => $code) {
                if (!empty($code)) {
                    DB::table('person_skill')->insert([
                        'staff_code' => $staffCode,
                        'id' => $i++,
                        'category_code' => $category,
                        'code' => $code,
                        'period' => 0,
                        'start_day' => now(),
                        'created_at' => now(),
                        'update_at' => now(),
                    ]);
                }
            }
            Log::info('✅ person_skill 保存成功');
            // 9. 志望動機
            DB::table('person_resume_other')->where('staff_code', $staffCode)->delete();
            DB::table('person_resume_other')->insert([
                'staff_code' => $staffCode,
                'id' => 1,
                'subject' => $this->resumePreference['subject'],
                'commute_time' => $this->resumePreference['commute_time'],
                'wish_motive' => $this->resumePreference['wish_motive'],
                'hope_column' => $this->resumePreference['hope_column'],
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info('✅ person_resume_other 保存成功');
            // 10. 希望職種（大分類・小分類）
            DB::table('person_hope_job_type')->where('staff_code', $staffCode)->delete();
            if (!empty($this->hopeCondition['job_type_big_code']) || !empty($this->hopeCondition['job_type_small_code'])) {
                $jobTypeCode = str_pad($this->hopeCondition['job_type_big_code'], 2, '0', STR_PAD_LEFT)
                    . str_pad($this->hopeCondition['job_type_small_code'], 2, '0', STR_PAD_LEFT)
                    . '000';

                DB::table('person_hope_job_type')->insert([
                    'staff_code' => $staffCode,
                    'id' => 1,
                    'job_type_code' => $jobTypeCode,
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
            }

            // 11. 希望勤務地
            DB::table('person_hope_working_place')->where('staff_code', $staffCode)->delete();
            if (!empty($this->hopeCondition['prefecture_code'])) {
                DB::table('person_hope_working_place')->insert([
                    'staff_code' => $staffCode,
                    'id' => 1,
                    'prefecture_code' => $this->hopeCondition['prefecture_code'],
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
            }

            // 12. 希望給料（salary列にそのまま）
            DB::table('person_hope_working_condition')->where('staff_code', $staffCode)->delete();
            DB::table('person_hope_working_condition')->insert([
                'staff_code' => $staffCode,
                'yearly_income_min' => $this->hopeCondition['salary_type'] === 'yearly'
                    ? (($this->hopeCondition['yearly_income_min'] ?? 0) * 10000)  // ← 🔥 大量に保管する
                    : 0,
                'hourly_income_min' => $this->hopeCondition['salary_type'] === 'hourly' ? ($this->hopeCondition['hourly_income_min'] ?? 0) : 0,
            ]);

            DB::commit();
            Log::info('✅ すべて保存成功');
            Log::info('✅ Livewire save() method end successfully.');
            session()->flash('success', '保存に成功しました。');
            // $this->dispatch('alert', ['type' => 'success', 'message' => '保存に成功しました。']);
            $this->dispatch('saved', ['message' => '保存に成功しました。']);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('履歴書保存エラー', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->dispatch('alert', [
                'type' => 'error',
                'message' => $e->getMessage() ?: '保存中にエラーが発生しました。',
                'trace' => app()->environment('local') ? $e->getTraceAsString() : null,
            ]);
        }
    }
    public function addResumePreference()
    {
        $this->resumePreferences[] = [
            'subject' => '',
            'wish_motive' => '',
            'hope_column' => '',
            'commute_time' => '',
        ];
    }

    public function removeResumePreference($index)
    {
        unset($this->resumePreferences[$index]);
        $this->resumePreferences = array_values($this->resumePreferences);
    }


    public function render()
    {
        $prefectures2 = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->select('code as prefecture_code', 'detail as prefecture_name')
            ->get();
        $prefectures = DB::table('master_code')->where('category_code', 'Prefecture')->get();
        $schoolTypes = DB::table('master_code')->where('category_code', 'SchoolType')->get();
        $courseTypes = DB::table('master_code')->where('category_code', 'CourseType')->get();
        $entryTypes = DB::table('master_code')->where('category_code', 'EntryType')->get();
        $graduateTypes = DB::table('master_code')->where('category_code', 'GraduateType')->get();
        $industryTypes = DB::table('master_code')->where('category_code', 'IndustryTypeDsp')->get();
        $workingTypes = DB::table('master_code')->where('category_code', 'WorkingType')->get();
        $jobTypes = DB::table('master_job_type')->whereNotIn('middle_class_code',  ['00'])->get();
        $licenseGroups = DB::table('master_license')->select('group_code', 'group_name')->distinct()->get();

        return view('livewire.resume-basic-info', [
            'prefectures' => $prefectures,
            'prefectures2' => $prefectures2,
            'schoolTypes' => $schoolTypes,
            'courseTypes' => $courseTypes,
            'entryTypes' => $entryTypes,
            'graduateTypes' => $graduateTypes,
            'industryTypes' => $industryTypes,
            'workingTypes' => $workingTypes,
            'jobTypes' => $jobTypes,
            'licenseGroups' => $licenseGroups,
        ]);
    }
}
