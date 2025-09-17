<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

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
    public $jobTypes;
    public $licenses = [];
    public $licenseGroups = [];       // グループ
    public $licenseCategories = [];   // カテゴリ
    public $licenseNames = [];        // 資格名
    public $self_pr = '';
    public $skills = [];
    public $selectedSkill;
    public $allSkills = [];
    public $skillRows = [];
    public $resumePreference = [
        'subject' => '',
        'commute_time' => '',
        'wish_motive' => '',
        'hope_column' => '',
    ];

    public $marriage_flag, $dependent_number, $dependent_flag;
    public $big_class_code;
    public $middle_class_code;

    public function switchAccordion($section)
    {
        $this->activeAccordion = $section;
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
            $this->marriage_flag = $person->marriage_flag;
            $this->dependent_number = $person->dependent_number;
            $this->dependent_flag = $person->dependent_flag;
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
        // 🔥 学歴が存在しない場合、空の一行を追加
        if (empty($this->educations)) {
            $this->educations[] = [
                'school_name' => '',
                'school_type_code' => '',
                'entry_day_year' => '',
                'entry_day_month' => '',
                'graduate_day_year' => '',
                'graduate_day_month' => '',
                'speciality' => '',
                'course_type' => '',
                'entry_type_code' => '',
                'graduate_type_code' => '',
            ];
        }


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
                    'capital' => $job->capital,
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

        // 🔥 もしDBにないなら、空の1行を追加
        if (empty($this->careers)) {
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
        $this->jobTypes = DB::table('job_job_type')->get();

        // 🔹 職種マスター
        $this->jobTypes = DB::table('master_job_type')
            ->select('big_class_code', 'middle_class_code', 'big_class_name', 'middle_clas_name')
            ->orderBy('big_class_code')
            ->orderBy('middle_class_code')
            ->get();

        // 🔹 資格
        $this->licenseGroups = DB::table('master_license')->select('group_code', 'group_name')->distinct()->get();
        $this->licenseCategories = DB::table('master_license')
            ->select('group_code', 'category_code', 'category_name')
            ->distinct()
            ->get()
            ->groupBy('group_code');
        $this->licenseNames = DB::table('master_license')
            ->select('group_code', 'category_code', 'code', 'name')
            ->distinct()
            ->get()
            ->groupBy(function ($item) {
                return $item->group_code . '_' . $item->category_code;
            });

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
        // 🔥 もしDBにないなら、空の1行を追加 (LICENSE)
        if (empty($this->licenses)) {
            $this->licenses[] = [
                'id' => null,
                'group_code' => '',
                'category_code' => '',
                'code' => '',
                'get_day' => '',
            ];
        }

        // 🔹 自己PR
        $self = DB::table('person_self_pr')->where('staff_code', $staffCode)->first();
        $this->self_pr = $self->self_pr ?? '';

        // 🔹 スキル
        $this->allSkills = DB::table('master_code')
            ->whereIn('category_code', ['OS', 'Application', 'DevelopmentLanguage', 'Database'])
            ->select('category_code', 'code', 'detail')
            ->get()
            ->groupBy('category_code')
            ->toArray();

        $savedSkills = DB::table('person_skill')
            ->where('staff_code', $staffCode)
            ->get();

        $groupedRows = [];
        foreach ($savedSkills as $skill) {
            $found = false;
            foreach ($groupedRows as &$row) {
                if (empty($row[$skill->category_code])) {
                    $row[$skill->category_code] = $skill->code;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $groupedRows[] = [
                    'OS' => $skill->category_code == 'OS' ? $skill->code : '',
                    'Application' => $skill->category_code == 'Application' ? $skill->code : '',
                    'DevelopmentLanguage' => $skill->category_code == 'DevelopmentLanguage' ? $skill->code : '',
                    'Database' => $skill->category_code == 'Database' ? $skill->code : '',
                ];
            }
        }
        $this->skillRows = $groupedRows;
        if (empty($this->skillRows)) {
            $this->addSkillRow();
        }

        // 🔹 その他 (resume preferences)
        $other = DB::table('person_resume_other')->where('staff_code', $staffCode)->first();
        if ($other) {
            $this->resumePreference['subject'] = $other->subject;
            $this->resumePreference['commute_time'] = $other->commute_time;
            $this->resumePreference['wish_motive'] = $other->wish_motive;
            $this->resumePreference['hope_column'] = $other->hope_column;
        }
    }

    public function addEducationRow()
    {
        if (count($this->educations) < 3) {
            $this->educations[] = [
                'school_name' => '',
                'school_type_code' => '',
                'entry_day_year' => '',
                'entry_day_month' => '',
                'graduate_day_year' => '',
                'graduate_day_month' => '',
                'speciality' => '',
                'course_type' => '',
                'entry_type_code' => '',
                'graduate_type_code' => '',
            ];
        } else {
            $this->dispatch('error', '学歴は最大3件まで追加できます。');
        }
    }


    public function removeEducationRow($index)
    {
        unset($this->educations[$index]);
        $this->educations = array_values($this->educations);
    }
    public function updatedCareers($value, $name)
    {
        [$index, $field] = explode('.', $name);

        // Agar 大分類 (big_class_code) o'zgarsa, 小分類 (middle_class_code) ni tozalaymiz
        if ($field === 'job_type_big_code') {
            $this->careers[$index]['job_type_small_code'] = '';
        }
    }
    public function getSmallJobTypes($bigClassCode)
    {
        return $this->jobTypes->where('big_class_code', $bigClassCode)->values();
    }

    public function addCareerRow()
    {
        if (count($this->careers) < 10) {
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
        } else {
            $this->dispatch('error', '職歴は最大10件まで追加できます。');
        }
    }


    public function removeCareerRow($index)
    {
        unset($this->careers[$index]);
        $this->careers = array_values($this->careers);
    }
    public function addLicenseRow()
    {
        if (count($this->licenses) < 10) {
            $this->licenses[] = [
                'id' => null,
                'group_code' => '',
                'category_code' => '',
                'code' => '',
                'get_day' => '',
            ];
        } else {
            $this->dispatch('error', '資格は最大10件まで追加できます。');
        }
    }


    public function removeLicenseRow($index)
    {
        unset($this->licenses[$index]);
        $this->licenses = array_values($this->licenses);
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

    public function updatedSkillRows()
    {
        $skills = [];

        foreach ($this->skillRows as $row) {
            foreach ($row as $category => $code) {
                if (!empty($code)) {
                    $detail = collect($this->allSkills[$category] ?? [])->firstWhere('code', $code);
                    if ($detail) {
                        $skills[] = [
                            'category' => $category,
                            'code' => $code,
                            'detail' => $detail->detail,
                        ];
                    }
                }
            }
        }

        $this->skills = $skills;
    }

    public function addSkillRow()
    {
        if (count($this->skillRows) < 10) {
            $this->skillRows[] = [
                'OS' => '',
                'Application' => '',
                'DevelopmentLanguage' => '',
                'Database' => '',
            ];
        }
    }

    public function removeSkillRow($index)
    {
        unset($this->skillRows[$index]);
        $this->skillRows = array_values($this->skillRows);
    }

    public function save()
    {
        $staffCode = Auth::user()->staff_code;
        if (!$staffCode) {
            Log::warning("⚠️ ユーザー staff_code が存在しません。");
            return;
        }
        Log::info("✅ staff_code が特定されました。", ['staff_code' => $staffCode]);

        $validated = $this->validate([
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
            'self_pr' => 'nullable|string|max:200',
        ], [], [
            // 📌 カスタムメッセージ (Yapon tilida aniq errorlar)
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
            'self_pr.max' => '自己PRは2000文字以内で入力してください。',
        ]);


        DB::beginTransaction();
        Log::info("🔹 ResumeBasicInfo::save() が呼び出されました.");
        try {
            // ✅ 1. 画像をアップロード
            if ($this->photo) {
                $file = $this->photo->getRealPath();
                $mime = mime_content_type($file);
                $size = filesize($file);

                // サーバー側のMIMEとサイズのチェック
                if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png'])) {
                    throw new \Exception('許可されていないファイル形式です。JPEGまたはPNGファイルのみアップロードできます。');
                }
                if ($size > 5 * 1024 * 1024) { // 5MBの制限
                    throw new \Exception('ファイルサイズは5MB以内にしてください。');
                }

                $imageData = file_get_contents($file);

                DB::table('person_picture')->updateOrInsert(
                    ['staff_code' => $staffCode],
                    [
                        'picture' => $imageData,
                        'created_at' => now(),
                        'update_at' => now(),
                    ]
                );
                Log::info("✅ 写真が保存されました。", ['staff_code' => $staffCode]);
            }

            // ✅ 2. master_person update
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
                    'portable_telephone_number' => $this->portable_telephone_number,
                    'address' => $this->address,
                    'address_f' => $this->address_f,
                    'city' => $this->city,
                    'city_f' => $this->city_f,
                    'town' => $this->town,
                    'town_f' => $this->town_f,
                    'updated_at' => now(),
                ]
            );
            Log::info("✅ 基本情報が保存されました。", ['staff_code' => $staffCode]);

            // ✅ 3. person_educate_history - DELETE → INSERT
            DB::table('person_educate_history')->where('staff_code', $staffCode)->delete();
            Log::info("🗑 学歴データ削除完了。", ['staff_code' => $staffCode]);
            $educationId = 1;
            foreach ($this->educations as $edu) {
                DB::table('person_educate_history')->insert([
                    'staff_code' => $staffCode,
                    'id' => $educationId++,
                    'school_name' => $edu['school_name'],
                    'school_type_code' => $edu['school_type_code'],
                    'entry_day' => $edu['entry_day_year'] . '-' . $edu['entry_day_month'] . '-01',
                    'graduate_day' => $edu['graduate_day_year'] . '-' . $edu['graduate_day_month'] . '-01',
                    'speciality' => $edu['speciality'],
                    'course_type' => $edu['course_type'],
                    'entry_type_code' => $edu['entry_type_code'],
                    'graduate_type_code' => $edu['graduate_type_code'],
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
            }
            Log::info("✅ 学歴情報が保存されました。", ['staff_code' => $staffCode]);

            // ✅ 4. person_career_history - DELETE → INSERT
            DB::table('person_career_history')->where('staff_code', $staffCode)->delete();
            Log::info("🗑 職歴データ削除完了。", ['staff_code' => $staffCode]);
            $careerId = 1;
            foreach ($this->careers as $career) {
                $jobTypeCode = str_pad($career['job_type_big_code'], 2, '0', STR_PAD_LEFT)
                    . str_pad($career['job_type_small_code'], 2, '0', STR_PAD_LEFT)
                    . '000';

                DB::table('person_career_history')->insert([
                    'staff_code' => $staffCode,
                    'id' => $careerId++,
                    'company_name' => $career['company_name'],
                    'capital' => $career['capital'],
                    'number_employees' => $career['number_employees'],
                    'entry_day' => $career['entry_day_year'] . '-' . $career['entry_day_month'] . '-01',
                    'retire_day' => $career['retire_day_year'] . '-' . $career['retire_day_month'] . '-01',
                    'industry_type_code' => $career['industry_type_code'],
                    'working_type_code' => $career['working_type_code'],
                    'job_type_detail' => $career['job_type_detail'],
                    'job_type_code' => $jobTypeCode, // ✅ ← これ！
                    'business_detail' => $career['business_detail'],
                    'created_atday' => now(),
                    'update_at' => now(),
                ]);
            }

            Log::info("✅ 職歴情報が保存されました。", ['staff_code' => $staffCode]);

            // ✅ person_license DELETE
            DB::table('person_license')->where('staff_code', $staffCode)->delete();
            Log::info("🗑 資格データ削除完了。", ['staff_code' => $staffCode]);

            $licenseId = 1;

            // ✅ person_license INSERT
            foreach ($this->licenses as $license) {
                // 必要なデータがそろっている場合のみ保存
                if (!empty($license['group_code']) && !empty($license['category_code']) && !empty($license['code'])) {

                    // master_license から資格名を取得する
                    $licenseName = DB::table('master_license')
                        ->where('group_code', $license['group_code'])
                        ->where('category_code', $license['category_code'])
                        ->where('code', $license['code'])
                        ->value('name') ?? '';

                    DB::table('person_license')->insert([
                        'staff_code' => $staffCode,
                        'id' => $licenseId++,
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
            Log::info("✅ 資格情報が保存されました。", ['staff_code' => $staffCode]);

            // ✅ 6. person_skill - DELETE → INSERT
            DB::table('person_skill')->where('staff_code', $staffCode)->delete();
            $skillId = 1;
            foreach ($this->skillRows as $row) {
                foreach ($row as $category => $skillCode) {
                    if (!empty($skillCode)) {
                        DB::table('person_skill')->insert([
                            'staff_code' => $staffCode,
                            'id' => $skillId++,
                            'category_code' => $category,
                            'code' => $skillCode,
                            'period' => 0,
                            'start_day' => now(),
                            'created_at' => now(),
                            'update_at' => now(),
                        ]);
                    }
                }
            }
            Log::info("✅ スキル情報が保存されました。", ['staff_code' => $staffCode]);

            // ✅ 7. person_self_pr - DELETE → INSERT
            DB::table('person_self_pr')->where('staff_code', $staffCode)->delete();
            DB::table('person_self_pr')->insert([
                'staff_code' => $staffCode,
                'self_pr' => $this->self_pr,
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info("✅ 自己PRが保存されました。", ['staff_code' => $staffCode]);

            // ✅ 8. person_resume_other - DELETE → INSERT
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
            Log::info("✅ 志望動機が保存されました。", ['staff_code' => $staffCode]);

            DB::commit();
            Log::info("✅ すべてのデータが正常に保存されました。", ['staff_code' => $staffCode]);
            // 🎉 Save success
            $this->dispatch('success', '保存に成功しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('保存エラー: ' . $e->getMessage());
            Log::error("❌ 保存に失敗しました: " . $e->getMessage(), ['staff_code' => $staffCode, 'trace' => $e->getTraceAsString()]);
            // ❌ Save error
            $this->dispatch('error', 'エラーが発生しました。');
        }
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
        $jobTypes = DB::table('master_job_type')->whereNotIn('middle_class_code',  ['00'])->get();
        $licenseGroups = DB::table('master_license')->select('group_code', 'group_name')->distinct()->get();

        return view('livewire.resume-basic-info', [
            'prefectures' => $prefectures,
            'schoolTypes' => $schoolTypes,
            'courseTypes' => $courseTypes,
            'entryTypes' => $entryTypes,
            'graduateTypes' => $graduateTypes,
            'industryTypes' => $industryTypes,
            'workingTypes' => $workingTypes,
            'jobTypes' => $this->jobTypes,
            'licenseGroups' => $licenseGroups,
            'licenseCategories' => $this->licenseCategories,  // ✅ <-- BU KERAK!
            'licenseNames' => $this->licenseNames,            // ✅ <-- BU KERAK!
            'skills' => $this->skills,
            'allSkills' => $this->allSkills,
        ]);
    }
}
