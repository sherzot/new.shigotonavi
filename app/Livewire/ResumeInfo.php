<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\MasterPerson;
use App\Models\PersonUserInfo;
use App\Mail\VerifyEmail;
use Carbon\Carbon;

class ResumeInfo extends Component
{
    use WithFileUploads;

    public $activeAccordion = 'basic';
    public $name, $name_f, $mail_address, $password;
    public $birthday, $sex, $post_u, $post_l, $prefecture_code;
    public $address, $address_f, $city, $city_f, $town, $town_f;

    public $portable_telephone_number;
    public $home_telephone_number;
    public $marriage_flag, $dependent_number, $dependent_flag;
    public $photo, $existingPhoto;
    public $educations = [], $careers = [], $licenses = [], $skills = [];

    public $resumePreference = [
        'subject' => '',
        'commute_time' => '',
        'wish_motive' => '',
        'hope_column' => ''
    ];
    public $hopeCondition = [
        'job_type_big_code' => '',
        'job_type_small_code' => '',
        'prefecture_code' => '',
        'salary_type' => '',
        'yearly_income_min' => '',
        'hourly_income_min' => ''
    ];
    public $jobTypes = [];

    public $self_pr = '';
    public $licenseGroups = [], $licenseCategories = [], $licenseNames = [], $skillCategories = [], $allSkills = [];

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
            return collect(); // 空のコレクションを返します。
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
        // 🔹 1. Auth user tekshiruv
        $user = Auth::user();

        // 🔸 Agar ro‘yxatdan o‘tgan bo‘lsa (Auth user mavjud bo‘lsa)
        if ($user && $user->staff_code) {
            $staffCode = $user->staff_code;

            // 🔹 基本情報 yuklash
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
                $this->home_telephone_number = $person->home_telephone_number;
            }
            // 🔹 写真
            $photoRecord = DB::table('person_picture')->where('staff_code', $staffCode)->first();
            if ($photoRecord && $photoRecord->picture) {
                $mime = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $photoRecord->picture);
                $this->existingPhoto = 'data:' . $mime . ';base64,' . base64_encode($photoRecord->picture);
            }

            // 🔹 PR va 希望条件 bo‘lsa ularni ham yuklash (agar kerak bo‘lsa)
            $other = DB::table('person_resume_other')->where('staff_code', $staffCode)->first();
            if ($other) {
                $this->resumePreference['subject'] = $other->subject;
                $this->resumePreference['commute_time'] = $other->commute_time;
                $this->resumePreference['wish_motive'] = $other->wish_motive;
                $this->resumePreference['hope_column'] = $other->hope_column;
            }
            $this->marriage_flag = $person->marriage_flag;
            $this->dependent_number = $person->dependent_number;
            $this->dependent_flag = $person->dependent_flag;
        }
        // 🔹 💥 jobTypes をここで読み込む
        $this->jobTypes = DB::table('master_job_type')
            ->whereNotIn('middle_class_code', ['00'])
            ->get();
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
        Log::info('📥 save() method called');
        Log::info('📥 入力内容:', [
            'name' => $this->name,
            'name_f' => $this->name_f,
            'mail_address' => $this->mail_address,
            'birthday' => $this->birthday,
            'sex' => $this->sex,
            'post_u' => $this->post_u,
            'post_l' => $this->post_l,
            'prefecture_code' => $this->prefecture_code,
            'portable_telephone_number' => $this->portable_telephone_number,
            'home_telephone_number' => $this->home_telephone_number,
            'address' => $this->address,
            'address_f' => $this->address_f,
            'city' => $this->city,
            'city_f' => $this->city_f,
            'town' => $this->town,
            'town_f' => $this->town_f,
            'marriage_flag' => $this->marriage_flag,
            'dependent_number' => $this->dependent_number,
            'dependent_flag' => $this->dependent_flag,
            'self_pr' => $this->self_pr,
            'resumePreference' => $this->resumePreference,
            'educations' => $this->educations,
            'careers' => $this->careers,
            'licenses' => $this->licenses,
        ]);
        $rules = [
            'name' => 'required|string|max:255',
            'name_f' => 'required|string|max:255',
            'mail_address' => 'required|email|unique:master_person,mail_address',
            'password' => 'required|min:3',
            'birthday' => 'required|digits:8',
            'portable_telephone_number' => 'required|string',
            'home_telephone_number' => 'nullable|string',
            'sex' => 'required|in:1,2',
            'post_u' => 'required|size:3',
            'post_l' => 'required|size:4',
            'prefecture_code' => 'required|string',

            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:500',
            
            'self_pr' => 'nullable|string|max:224',
            // 👉 school_name は 1 つだけで十分です。
            'educations' => 'required|array|min:1',
            'educations.*.school_name' => 'required|string|max:255',

            // 👉 company_name は 1 つだけで十分です。
            'careers' => 'required|array|min:1',
            'careers.*.company_name' => 'required|string|max:255',

            'resumePreference.wish_motive' => 'nullable|string|max:224',
            'resumePreference.hope_column' => 'nullable|string|max:224',
            'resumePreference.subject' => 'nullable|string|max:224',
        ];
        $customMessages = [
            'name.required' => 'お名前を入力してください。',
            'name.string' => 'お名前は文字列で入力してください。',
            'name.max' => 'お名前は255文字以内で入力してください。',

            'name_f' => 'お名前（フリガナ）',

            'mail_address.required' => 'メールアドレスを入力してください。',
            'mail_address.email' => '正しいメールアドレス形式で入力してください。',
            'mail_address.unique' => 'このメールアドレスは既に登録されています。',

            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは3文字以上で入力してください。',

            'birthday.required' => '生年月日を入力してください。',
            'birthday.digits' => '生年月日は8桁（例：19900101）で入力してください。',

            'sex' => '性別',

            'post_u.required' => '郵便番号（上3桁）を入力してください。',
            'post_u.size' => '郵便番号（上3桁）はちょうど3桁で入力してください。',

            'post_l.required' => '郵便番号（下4桁）を入力してください。',
            'post_l.size' => '郵便番号（下4桁）はちょうど4桁で入力してください。',

            'prefecture_code.required' => '都道府県を選択してください。',

            'portable_telephone_number.required' => '電話番号を入力してください。',
            'home_telephone_number' => '緊急連絡先を入力してください。',

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

            'educations.required' => '学歴は最低1件必要です。',
            'educations.min' => '学歴は最低1件必要です。',
            'educations.*.school_name.required' => '学校名は必須です。',
            'careers.required' => '職歴は最低1件必要です。',
            'careers.min' => '職歴は最低1件必要です。',
            'careers.*.company_name.required' => '企業名は必須です。',

            'resumePreference.wish_motive.string' => '志望動機は文字列で入力してください。',
            'resumePreference.wish_motive.max' => '志望動機は200文字以内で入力してください。',
            'resumePreference.hope_column.string' => '本人希望欄は文字列で入力してください。',
            'resumePreference.hope_column.max' => '本人希望欄は200文字以内で入力してください。',
            'resumePreference.subject.string' => '志望動機の識別名は文字列で入力してください。',
            'resumePreference.subject.max' => '志望動機の識別名は200文字以内で入力してください。',
        ];
        
        if (!Auth::check()) {
            // 🆕 新規ユーザー: メールアドレスは一意である必要があります。パスワードも必要です。
            $rules['mail_address'] = 'required|email|unique:master_person,mail_address';
            $rules['password'] = 'required|min:3';
        }
        // dd($rules);
        $validator = \Validator::make(
            $this->only(array_keys($rules)), // ルール内のフィールドのみ取得します
            $rules,
            $customMessages
        );
    
        if ($validator->fails()) {
            $this->setErrorBag($validator->getMessageBag());
            return;
        }
        
        // 🔍 学歴 (education) ichida kamida 1 ta school_name borligini tekshirish
        // if (collect($this->educations)->filter(fn($e) => !empty($e['school_name']))->count() === 0) {
        //     $this->addError('educations.0.school_name', '学歴は最低1件必要です。');
        // }
        // // dd($e);

        // // 🔍 職歴 (career) ichida kamida 1 ta company_name borligini tekshirish
        // if (collect($this->careers)->filter(fn($c) => !empty($c['company_name']))->count() === 0) {
        //     $this->addError('careers.0.company_name', '職歴は最低1件必要です。');
        // }

        // ❌ Xato bo‘lsa, saqlashni to‘xtatamiz
        // if ($this->getErrorBag()->isNotEmpty()) {
        //     return;
        // }

        $this->validate($rules, $customMessages);
        // 検証されたデータを記録します。
        DB::beginTransaction();
        try {
            // 🔹 スタッフコード生成と新規ユーザー
            if (!Auth::check()) {
                $lastId = DB::table('master_person')->max(DB::raw("CAST(SUBSTRING(staff_code, 2) AS UNSIGNED)"));
                $nextId = $lastId ? $lastId + 1 : 1;
                $staffCode = 'S' . str_pad($nextId, 7, '0', STR_PAD_LEFT);
                // 1.master_person insert
                $person = MasterPerson::create([
                    'staff_code' => $staffCode,
                    'mail_address' => $this->mail_address,
                    'name' => $this->name,
                    'birthday' => Carbon::createFromFormat('Ymd', $this->birthday)->format('Y-m-d'),
                    'portable_telephone_number' => $this->portable_telephone_number,
                    'age' => Carbon::parse($this->birthday)->age,
                    'regist_commit' => 1,
                ]);
                // 2.person_userinfo insert
                PersonUserInfo::updateOrCreate(
                    ['staff_code' => $staffCode],
                    [
                        'password' => strtoupper(md5($this->password)),
                        'regist_commit' => 1,
                        'created_at' => now(),
                    ]
                );

                Auth::login($person);
                session(['registered' => true]);

                // 3.メールを送信
                try {
                    Mail::to($person->mail_address)->send(new VerifyEmail($person));
                } catch (\Exception $e) {
                    Log::error("📧 メール送信エラー: " . $e->getMessage());
                }

                DB::table('log_person_signin')->insert([
                    'staff_code' => $staffCode,
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
            }
            $staffCode = Auth::user()->staff_code;
            // 4.master_person update
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
                'home_telephone_number' => $this->home_telephone_number,
                'marriage_flag' => $this->marriage_flag,
                'dependent_number' => $this->dependent_number,
                'dependent_flag' => $this->dependent_flag,
                'updated_at' => now(),
            ]);
            Log::info('✅ master_person 更新成功');
            Log::info('📥 ユーザー入力データ:', [
                'name' => $this->name,
                'name_f' => $this->name_f,
                'mail_address' => $this->mail_address,
                'birthday' => $this->birthday,
                'sex' => $this->sex,
                'post_u' => $this->post_u,
                'post_l' => $this->post_l,
                'prefecture_code' => $this->prefecture_code,
                'portable_telephone_number' => $this->portable_telephone_number,
                'home_telephone_number' => $this->home_telephone_number,
                'address' => $this->address,
                'address_f' => $this->address_f,
                'city' => $this->city,
                'city_f' => $this->city_f,
                'town' => $this->town,
                'town_f' => $this->town_f,
                'marriage_flag' => $this->marriage_flag,
                'dependent_number' => $this->dependent_number,
                'dependent_flag' => $this->dependent_flag,
                'self_pr' => $this->self_pr,
                'resumePreference' => $this->resumePreference,
                'educations' => $this->educations,
                'careers' => $this->careers,
                'licenses' => $this->licenses,
            ]);

            // 5. Photo upload
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
            // 🔒 各教育行をチェック
            foreach ($this->educations as $index => $edu) {
                if (empty($edu['school_name'])) {
                    $this->addError("educations.$index.school_name", '学校名は必須です');
                }
            }
            // ❌ エラーがある場合は保存されません。
            if ($this->getErrorBag()->isNotEmpty()) {
                return;
            }
            // 6. 学歴情報 (education)
            // DB::table('person_educate_history')->where('staff_code', $staffCode)->delete();
            foreach ($this->educations as $index => $edu) {
                // if (empty($edu['school_name'])) {
                //     $this->addError("educations.$index.school_name", '学校名は必須です');
                //     throw new \Exception("学歴のエラーが発生しました。");
                // }

                DB::table('person_educate_history')->insert([
                    'staff_code' => $staffCode,
                    'id' => $index++,
                    'school_name' => $edu['school_name'],
                    'school_type_code' => $edu['school_type_code'],
                    'entry_day' => "{$edu['entry_day_year']}-{$edu['entry_day_month']}-01",
                    'graduate_day' => "{$edu['graduate_day_year']}-{$edu['graduate_day_month']}-01",
                    'speciality' => $edu['speciality'] ?? '',
                    'course_type' => $edu['course_type'] ?? '',
                    'entry_type_code' => $edu['entry_type_code'] ?? '',
                    'graduate_type_code' => $edu['graduate_type_code'] ?? '',
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
            }
            Log::info('🔹 person_career_history 保存開始');

            // 🔒 各職歴行をチェック
            foreach ($this->careers as $index => $career) {
                if (empty($career['company_name'])) {
                    $this->addError("careers.$index.company_name", '企業名は必須です');
                }
            }
            if ($this->getErrorBag()->isNotEmpty()) {
                return;
            }

            // 7. 職歴情報 (career)
            // DB::table('person_career_history')->where('staff_code', $staffCode)->delete();
            foreach ($this->careers as $index => $career) {
                // if (empty($career['company_name'])) {
                //     $this->addError("careers.$index.company_name", '企業名は必須です');
                //     throw new \Exception("職歴のエラーが発生しました。");
                // }

                $jobTypeCode = str_pad($career['job_type_big_code'], 2, '0', STR_PAD_LEFT)
                    . str_pad($career['job_type_small_code'], 2, '0', STR_PAD_LEFT)
                    . '000';

                DB::table('person_career_history')->insert([
                    'staff_code' => $staffCode,
                    'id' => $index++,
                    'company_name' => $career['company_name'],
                    'capital' => intval($career['capital']) * 10000,
                    'number_employees' => $career['number_employees'],
                    'entry_day' => "{$career['entry_day_year']}-{$career['entry_day_month']}-01",
                    'retire_day' => "{$career['retire_day_year']}-{$career['retire_day_month']}-01",
                    'industry_type_code' => $career['industry_type_code'],
                    'working_type_code' => $career['working_type_code'],
                    'job_type_code' => $jobTypeCode,
                    'job_type_detail' => $career['job_type_detail'],
                    'business_detail' => $career['business_detail'],
                    'created_atday' => now(),
                    'update_at' => now(),
                ]);
            }
            Log::info('✅ person_career_history 保存成功');


            // 8. 資格情報 (licenses)
            DB::table('person_license')->where('staff_code', $staffCode)->delete();
            foreach ($this->licenses as $index => $license) {
                if (!empty($license['group_code']) && !empty($license['category_code']) && !empty($license['code'])) {
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
            // 9. 自己PR
            DB::table('person_self_pr')->where('staff_code', $staffCode)->delete();
            DB::table('person_self_pr')->updateOrInsert(
                ['staff_code' => $staffCode],
                ['self_pr' => $this->self_pr, 'created_at' => now(), 'update_at' => now()]
            );
            Log::info('✅ person_self_pr 保存成功');
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

            DB::commit();
            Log::info('✅ すべて保存成功');
            Log::info('✅ Livewire save() method end successfully.');
            session()->flash('success', '保存に成功しました。');

            $this->dispatch('resumeCompleted'); // Livewireブロックを更新するには

            // ✅ Alert yuborish
            $this->dispatch('saved', message: '履歴書が正常に保存されました。');
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

        return view('livewire.resume-info', [
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



// livewire.resume-info