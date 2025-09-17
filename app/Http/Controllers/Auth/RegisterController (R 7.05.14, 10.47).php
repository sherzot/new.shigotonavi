<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\InitialVerifyEmail;
use App\Mail\VerifyEmail;
use App\Models\MasterPerson;
use App\Models\PersonUserInfo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\PersonHopeWorkingCondition;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Transliterator;

class RegisterController extends Controller
{
    public function landing()
    {
        $jobCount = DB::table('job_order')
            ->where('public_flag', 1)
            ->where('order_progress_type', 1)
            ->where('public_limit_day', '>=', now())
            ->distinct('order_code')
            ->count('order_code');

        $companyCount = DB::table('master_company')->count('company_code');
        $userCount = DB::table('master_person')->count('staff_code');

        return view('landing', compact('jobCount', 'companyCount', 'userCount'));
    }
    public function showEmailCreate()
    {
        return view("signin");
    }
    // public function getAddressFromZipcloud(Request $request)
    // {
    //     $zipcode = $request->post_u . $request->post_l;

    //     if (strlen($zipcode) !== 7) {
    //         return response()->json(['error' => '郵便番号は7桁である必要があります。'], 400);
    //     }

    //     $response = Http::get("https://zipcloud.ibsnet.co.jp/api/search?zipcode=" . $zipcode);

    //     if ($response->successful()) {
    //         $data = $response->json();

    //         if (!empty($data['results'])) {
    //             $city = $data['results'][0]['address2'];  // 市区町村
    //             $town = $data['results'][0]['address3'];  // 町名
    //             return response()->json([
    //                 'city' => $city,
    //                 'town' => $town,
    //                 'full_address' => $data['results'][0]['address1'] . ' ' . $city . ' ' . $town
    //             ]);
    //         }
    //     }
    //     return response()->json(['error' => '郵便番号が見つかりませんでした。'], 404);
    // }
    public function registration(Request $request)
    {
        Log::info("会員登録開始 (User Registration Started)");

        // 🔹 1️⃣ フォームデータの検証 (VALIDATSIYA)
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'mail_address' => 'required|email|unique:master_person,mail_address',
            'password' => 'required|string|min:3',
            'portable_telephone_number' => 'required|string',
            'birthday' => 'required|digits:8',
            // 'post_u' => 'required|size:3',
            // 'post_l' => 'required|size:4',
        ]);
        // 📌 **メール認証**
        $exists = DB::table('master_person')->where('mail_address', $request->mail_address)->exists();
        if ($exists) {
            Log::warning('❌ このメールアドレスは既に登録されています: ' . $request->mail_address);
            return redirect()->back()->withErrors(['mail_address' => 'このメールアドレスは既に登録されています。'])->withInput();
        }
        Log::info("✅ フォームデータの検証完了");


        // 🔹 2️⃣ 郵便番号から住所を取得する
        // $zipcode = $request->post_u . $request->post_l;
        // Log::info("📌 郵便番号取得: {$zipcode}");

        // $response = Http::get("https://zipcloud.ibsnet.co.jp/api/search?zipcode=" . $zipcode);
        // $prefecture = '';
        // $city = '';
        // $town = '';
        // $prefectureCode = null; // 初期値

        // if ($response->successful()) {
        //     $data = $response->json();
        //     if (!empty($data['results'])) {
        //         $prefecture = $data['results'][0]['address1'];
        //         $city = $data['results'][0]['address2'];
        //         $town = $data['results'][0]['address3'];
        //     }
        // }

        // // 🌟 master_codeテーブルから都道府県コードを取得する
        // if ($prefecture) {
        //     $prefectureCode = DB::table('master_code')
        //         ->where('category_code', 'Prefecture')
        //         ->where('detail', $prefecture)
        //         ->value('code');
        // }

        // // ❌ 都道府県コードまたは市区町村が見つからない場合は、ユーザーを返します
        // if (!$city || !$prefectureCode) {
        //     Log::error("❌ 郵便番号から住所が取得できませんでした: {$zipcode}");
        //     return redirect()->back()->withErrors(['zipcode' => '郵便番号が見つかりませんでした。'])->withInput();
        // }

        // Log::info("✅ 住所取得完了: {$prefecture}, {$city}, {$town}, Prefecture Code: {$prefectureCode}");

        // // 🔹 3️⃣ 完全な住所から追加の住所を抽出する
        // $fullAddressInput = $request->full_address ?? ''; // ユーザーが入力した完全な住所
        // $standardAddress = trim("{$prefecture} {$city} {$town}"); // API経由で受信したアドレス

        // $extraAddress = str_replace($standardAddress, '', $fullAddressInput); // 追加のアドレスを割り当てる
        // $extraAddress = trim($extraAddress); // 先頭と末尾のスペースを削除する

        // Log::info("📌 住所: {$fullAddressInput}");
        // Log::info("📌 追加住所: {$extraAddress}");

        try {
            DB::beginTransaction(); // 取引が開始されました

            // ✅ staff_codeの最大の数値部分を取り、新しいIDを作成します。
            $lastId = DB::table('master_person')->max(DB::raw("CAST(SUBSTRING(staff_code, 2) AS UNSIGNED)"));
            $nextId = $lastId ? $lastId + 1 : 1;
            $staffCode = 'S' . str_pad($nextId, 7, '0', STR_PAD_LEFT);

            Log::info("新しいスタッフコード: {$staffCode}");

            //🔹 ユーザーをデータベースに追加する
            $person = MasterPerson::create([
                'staff_code' => $staffCode,
                'mail_address' => $request->mail_address,
                'name' => $request->name,
                'portable_telephone_number' => $request->portable_telephone_number,
                'birthday' => Carbon::createFromFormat('Ymd', $request->birthday)->format('Y-m-d'),
                'age' => Carbon::parse($request->birthday)->age,
                // 'post_u' => $request->post_u,
                // 'post_l' => $request->post_l,
                // 'prefecture_code' => $prefectureCode,
                // 'city' => $city,
                // 'town' => $town,
                // 'address' => $extraAddress,
                'regist_commit' => 1,
            ]);

            Log::info('📌 master_person insertデータ: ', $person->toArray());

            Log::info("会員登録成功: Staff Code: {$staffCode}");
            $insertUserInfo = [
                'staff_code' => $staffCode,
                'password' => strtoupper(md5($request->password)),
                'regist_commit' => 1,
                'created_at' => now(),
                'update_at' => now(),
            ];

            // 🔸 ログ出力（ログファイルに書き込む）
            Log::info('📌 person_userinfo insertデータ: ', $insertUserInfo);

            PersonUserInfo::updateOrCreate(
                ['staff_code' => $staffCode],
                [
                    'password' => strtoupper(md5($request->password)),
                    'regist_commit' => 1,
                    'created_at' => now(),
                    // update_at は boot() 経由で自動的に追加されます
                ]
            );


            DB::commit(); // ✅ 取引が完了しました

            // 🔹 メールを送信
            $person = MasterPerson::where('staff_code', $staffCode)->first();
            Auth::login($person);

            // 🔹 確認メールを送信
            try {
                Mail::to($person->mail_address)->send(new VerifyEmail($person));
                Log::info("確認メール送信成功: {$person->mail_address}");
                
            } catch (\Exception $e) {
                Log::error("メール送信エラー: " . $e->getMessage());
            }

            return redirect()->route('matchings.match')->with('success', '登録完了！マイページへ移動しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ 登録エラー: " . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => '登録に失敗しました。もう一度試してください。'])
                ->withInput();
        }
    }
    private function checkboxOptions()
    {
        return [
            'inexperienced_person_flag' => '未経験者OK',
            'balancing_work_flag' => '仕事と生活のバランス',
            'ui_turn_flag' => 'UIターン',
            'many_holiday_flag' => '休日120日',
            'flex_time_flag' => 'フレックス',
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
            //'find_job_festive_flag' => '就職祝金',//20250402
            'appointment_flag' => '正社員登録',
            'license_acquisition_support_flag' => '資格取得支援あり'
        ];
    }
    public function showMatchCreate()
    {
        if (!Auth::check()) {
            Log::warning("❌ 未ログインのユーザーがアクセスを試みました。");
            return redirect()->route('login.form')->withErrors(['error' => 'ログインが必要です。']);
        }
        $person = Auth::user()->staff_code;
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
        // Job Types (職種)
        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();

        // License Groups (資格グループ)
        $groups = DB::table('master_license')
            ->select('group_code', 'group_name')
            ->distinct()
            ->get();

        return view('matchings.match', compact(
            'bigClasses',
            'groups',
            'prefectures',
            'regionGroups', // 地域
            'individualPrefectures', // 各都道府県
            'selectedPrefectures',
        ));
    }
    public function createMatchStore(Request $request)
    {
        if (!Auth::check()) {
            Log::warning("❌ 未ログインのユーザーがアクセスを試みました。");
            return redirect()->route('login.form')->withErrors(['error' => 'ログインが必要です。']);
        }
        $person = Auth::user()->staff_code;

        // 📌 入力されたデータの記録
        Log::info("📌 受信したリクエストデータ:", $request->all());

        $request->validate([
            'big_class_code' => 'required|exists:master_job_type,big_class_code',
            'job_category' => 'required|exists:master_job_type,middle_class_code',
            'prefecture_code' => 'required|array',
            // 'desired_salary_type' => 'required',
            'desired_salary_type' => 'required|in:年収,時給',
            'desired_salary_annual' => $request->desired_salary_type === '年収' ? 'required|integer|min:0' : 'nullable',
            'desired_salary_hourly' => $request->desired_salary_type === '時給' ? 'required|integer|min:0' : 'nullable',
            // 'desired_salary_annual' => 'nullable|integer|min:0',
            // 'desired_salary_hourly' => 'nullable|integer|min:0',
            'group_code' => 'nullable|string',
            'category_code' => 'nullable|string',
            'license_code' => 'nullable|string',
        ]);
        // dd($request);
        DB::enableQueryLog();
        // 🔹 8️⃣ 希望職種 (Job Type) 保存
        $bigClassName = DB::table('master_job_type')
            ->where('big_class_code', $request->big_class_code)
            ->value('big_class_name');

        if (!$bigClassName) {
            Log::error("Big class name not found for code: {$request->big_class_code}");
            return back()->withErrors(['big_class_code' => '選択した業種は存在しません。']);
        }

        $middleClassName = DB::table('master_job_type')
            ->where('middle_class_code', $request->job_category)
            ->where('big_class_code', $request->big_class_code)
            ->value('middle_clas_name');

        if (!$middleClassName) {
            Log::error("Middle class name not found or doesn't match big_class_code: {$request->job_category}");
            return back()->withErrors(['job_category' => '選択した職種タイプは存在しません。']);
        }

        $bigClassCode = $request->big_class_code;
        $middleClassCode = $request->job_category;

        $newJobTypeCode = $bigClassCode . $middleClassCode . "000";

        $jobTypeDetail = $middleClassName;
        // DB::table('person_hope_job_type')->where('staff_code', $person)->delete();
        $maxId = DB::table('person_hope_job_type')
            ->where('staff_code', $person)
            ->max('id');

        $newId = $maxId ? $maxId + 1 : 1;

        $newInsert = DB::table('person_hope_job_type')->insert([
            'staff_code' => $person,
            'id' => $newId,
            'job_type_code' => $newJobTypeCode,
            'job_type_detail' => $jobTypeDetail,
            'created_at' => now(),
            'update_at' => now(),
        ]);
        if ($newInsert) {
            Log::info("✅成功: person_hope_job_type: スタッフコードに挿入されました : {$person}, ID: {$newId}, Job Type Code: {$newJobTypeCode}, Job Type Detail: {$jobTypeDetail}");
        } else {
            Log::error("❌エラー: person_hope_job_type への挿入に失敗しました: スタッフコード : {$person}");
        }

        Log::info("person_hope_job_typeに挿入: スタッフコード: {$person}, ID: {$newId}, Job Type Code: {$newJobTypeCode}, Job Type Detail: {$jobTypeDetail}");

        // 🔹 9️⃣ 希望勤務地 (Preferred Work Location) 保存
        $prefectureCodes = is_array($request->prefecture_code)
            ? $request->prefecture_code
            : [$request->prefecture_code];
        DB::table('person_hope_working_place')->where('staff_code', $person)->delete();
        foreach ($prefectureCodes as $prefectureCode) {
            $maxId = DB::table('person_hope_working_place')
                ->where('staff_code', $person)
                ->max('id');
            $newId = $maxId ? $maxId + 1 : 1;

            DB::table('person_hope_working_place')->insert([
                'staff_code' => $person,
                'id' => $newId,
                'prefecture_code' => $prefectureCode,
                'city' => $request->city ?? null,
                'area' => $request->area ?? '日本',
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info("person_hope_working_place にエントリを挿入しました: スタッフコード: {$person}, ID: {$newId}, Prefecture Code: {$prefectureCode}");
        }

        // 🔹 🔟 希望給与 (Salary) 保存
        $desiredSalaryType = $request->input('desired_salary_type');
        $desiredSalaryAnnual = $request->input('desired_salary_annual')
            ? $request->input('desired_salary_annual') * 10000
            : null;
        $desiredSalaryHourly = $request->input('desired_salary_hourly') ?? null;

        Log::info("スタッフコードの処理済み給与条件: {$person}");

        // ✅ 新規ユーザーの場合、**すべての列は 0 またはデフォルトになります**
        PersonHopeWorkingCondition::updateSelectiveOrCreate($person, [
            'hourly_income_min' => $desiredSalaryType === '時給' ? $desiredSalaryHourly : 0,
            'yearly_income_min' => $desiredSalaryType === '年収' ? $desiredSalaryAnnual : 0,
        ]);


        Log::info("スタッフコードの person_hope_working_condition テーブルを更新しました: {$person}");

        // 🔹 1️⃣1️⃣ 資格 (ライセンス) ストレージ (利用可能な場合)
        if ($request->group_code && $request->category_code && $request->license_code) {
            // ライセンスコメントの「master_license」テーブルから列「name」を取得します。
            $licenseName = DB::table('master_license')
                ->where('group_code', $request->group_code)
                ->where('category_code', $request->category_code)
                ->where('code', $request->license_code)
                ->value('name');

            if (!$licenseName) {
                return back()->withErrors(['license_code' => '選択したライセンスは使用できません.']);
            }
            DB::table('person_license')->where('staff_code', $person)->delete();

            // 「person_license」テーブルの「id」を計算する
            $maxId = DB::table('person_license')
                ->where('staff_code', $person)
                ->max('id');

            $newId = $maxId ? $maxId + 1 : 1; // 存在する場合は +1、存在しない場合は 1 から開始します

            // ライセンスを person_license テーブルに保存します
            DB::table('person_license')->insert([
                'staff_code' => $person,
                'id' => $newId,
                'group_code' => $request->group_code,
                'category_code' => $request->category_code,
                'code' => $request->license_code,
                'remark' => $licenseName,
                'get_day' => now(),
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info("スタッフコードの person_license にライセンスデータを挿入しました: {$person}, ID: {$newId}");
        } else {
            Log::info("ライセンス データが提供されていません。スタッフコードの person_license 挿入をスキップします: {$person}");
        }


        Log::info("✅ 希望条件が登録されました！");
        Log::info("✅ データが保存されました: {$person}");

        return $this->showMatch($request);
    }
    public function showMatch(Request $request)
    {
        $person = Auth::user()->staff_code;

        // 📌 Job Types (希望職種)
        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();

        // 📌 License Groups (資格)
        $groups = DB::table('master_license')
            ->select('group_code', 'group_name')
            ->distinct()
            ->get();

        // 📌 Prefecture List (希望勤務地)
        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->get();
        // 📌 Checkbox options (特記事項)
        $checkboxOptions = $this->checkboxOptions();


        $personHopeWorkingPlaces = DB::table('person_hope_working_place')
            ->where('staff_code', $person)
            ->pluck('prefecture_code')
            ->toArray();

        $personHopeWorkingCondition = DB::table('person_hope_working_condition')
            ->where('staff_code', $person)
            ->select('hourly_income_min', 'yearly_income_min')
            ->first(); // 最初のレコードが取得されます。

        // 🔹 **ユーザーが保存した利用規約を取得する**
        $personHopeJobTypesCode = DB::table('person_hope_job_type')
            ->where('staff_code', $person)
            ->pluck('job_type_code')
            ->toArray(); // 🔹 Pluckを使用すると、結果は配列になります
        // 🔹 **条件がない場合、matchingJobsはNULLを返します**
        if (empty($personHopeJobTypesCode) || empty($personHopeWorkingPlaces) || !$personHopeWorkingCondition) {
            return view('matchings.showmatch', compact('bigClasses', 'groups', 'prefectures'))
                ->with('matchingJobs', null);
        }

        // 🔹 **ユーザーが以前に選択した証明書**
        $personLicense = DB::table('person_license')
            ->where('staff_code', $person)
            ->select('group_code', 'category_code', 'code')
            ->get();

        $groupCodes = $personLicense->pluck('group_code')->toArray();
        $categoryCodes = $personLicense->pluck('category_code')->toArray();
        $codes = $personLicense->pluck('code')->toArray();

        // 🎯 炭素による年齢計算
        $personBirthDay = DB::table('master_person')
            ->where('staff_code', $person)
            ->select('birthday')
            ->first();
        Log::info("📌 ユーザーの誕生日:", (array) $personBirthDay);

        $personAge = $personBirthDay ? Carbon::parse($personBirthDay->birthday)->age : null;
        Log::info("📌 計算年齢:", ['age' => $personAge]);

        $selectedFlags = $request->input('supplement_flags', []);
        $checkboxOptions = $this->checkboxOptions();
        Log::info("📌選択された補足フラグ: ", $selectedFlags);

        // 🔹 **マッチする仕事を見つける**
        DB::enableQueryLog(); // デバッグ用

        $matchingJobs = DB::table('job_order')
            ->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
            ->join('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
            ->leftJoin('job_license', 'job_order.order_code', '=', 'job_license.order_code')
            ->join('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
            ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
            ->join('master_code', function ($join) {
                $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                    ->where('master_code.category_code', '=', 'Prefecture');
            })
            ->whereIn('job_job_type.job_type_code', $personHopeJobTypesCode)
            ->whereIn('job_working_place.prefecture_code', $personHopeWorkingPlaces)
            ->when($personHopeWorkingCondition->yearly_income_min, function ($query) use ($personHopeWorkingCondition) {
                return $query->where('job_order.yearly_income_min', '>=', $personHopeWorkingCondition->yearly_income_min);
            })
            ->when($personHopeWorkingCondition->hourly_income_min, function ($query) use ($personHopeWorkingCondition) {
                return $query->where('job_order.hourly_income_min', '>=', $personHopeWorkingCondition->hourly_income_min);
            })
            // ->when(!empty($groupCodes), function ($query) use ($groupCodes) {
            //     return $query->whereIn('job_license.group_code', $groupCodes);
            // })
            // ->when(!empty($categoryCodes), function ($query) use ($categoryCodes) {
            //     return $query->whereIn('job_license.category_code', $categoryCodes);
            // })
            // ->when(!empty($codes), function ($query) use ($codes) {
            //     return $query->whereIn('job_license.code', $codes);
            // })
            ->where(function ($query) use ($personAge) {
                $query->where('job_order.age_max', '>=', $personAge)
                    ->orWhere('job_order.age_max', '=', '0');
            })
            ->where('job_order.public_flag', '=', 1)
            ->where('job_order.order_progress_type', '=', 1)
            ->where('job_order.public_limit_day', '>=', now())
            ->select([
                'job_order.order_code as id',
                'job_supplement_info.pr_title1',
                'job_supplement_info.pr_contents1',
                'job_order.job_type_detail',
                'master_company.company_name_k',
                'job_order.yearly_income_min',
                'job_order.yearly_income_max',
                'job_order.hourly_income_min',
                'job_order.hourly_income_max',
                'master_code.detail as prefecture_name',
                'job_order.update_at',
            ])
            ->addSelect(array_keys($checkboxOptions))
            ->groupBy(
                'job_order.order_code',
                'job_order.job_type_detail',
                'job_order.yearly_income_min',
                'job_order.yearly_income_max',
                'job_order.hourly_income_min',
                'job_order.hourly_income_max',
                'master_company.company_name_k',
                'master_code.detail',
                'job_working_place.city',
                'job_working_place.town',
                'job_job_type.job_type_code',
                'job_supplement_info.pr_title1',
                'job_supplement_info.pr_contents1',
            )
            ->orderBy('job_order.update_at', 'desc')
            ->distinct()
            ->paginate(6);

        Log::info(DB::getQueryLog()); // デバッグ用
        // チェックボックスのオプション
        $checkboxOptions = $this->checkboxOptions();

        foreach ($matchingJobs as $job) {
            $job->selectedFlagsArray = [];
            foreach ($checkboxOptions as $key => $label) {
                if (isset($job->$key) && $job->$key == 1) { // ⚠️ isset() でチェックする
                    $job->selectedFlagsArray[] = $key;
                }
            }
        }
        Log::info("📌 selectedFlagsArray: ", ['flags' => $matchingJobs->pluck('selectedFlagsArray')->toArray()]);

        if ($matchingJobs && count($matchingJobs) > 0) {
            $matchCount = $matchingJobs->total(); //count($matchingJobs);
            DB::table('log_person_signin')
                ->updateOrInsert(
                    ['staff_code' => $person],
                    fn($exists) => $exists ? [
                        'staff_code' => $person,
                        'match_count' => $matchCount,
                        'update_at' => now(),
                    ] : [
                        'staff_code' => $person,
                        'match_count' =>  $matchCount,
                        'created_at' => now(),
                        'update_at' => now(),
                    ],
                );
        } else {
            DB::table('log_person_signin')
                ->updateOrInsert(
                    ['staff_code' => $person],
                    fn($exists) => $exists ? [
                        'staff_code' => $person,
                        'match_count' => 0,
                        'update_at' => now(),
                    ] : [
                        'staff_code' => $person,
                        'match_count' =>  0,
                        'created_at' => now(),
                        'update_at' => now(),
                    ],
                );
        } // end if($matchingJobs)

        foreach ($matchingJobs as $result) {
            $result->yearly_income_display = "{$result->yearly_income_min}円〜" . ($result->yearly_income_max > 0 ? "{$result->yearly_income_max}円" : '');
            $result->hourly_income_display = "{$result->hourly_income_min}円〜" . ($result->hourly_income_max > 0 ? "{$result->hourly_income_max}円" : '');
        }

        return view('matchings.showmatch', compact('matchingJobs', 'bigClasses', 'groups', 'prefectures', 'checkboxOptions',));
    }
    public function filterJobs(Request $request)
    {
        Log::info("🔎 AJAX リクエストを受信しました:", $request->all());

        $person = Auth::user()->staff_code;

        $selectedFilters = $request->filters ?? [];
        Log::info("🔎 選択されたフィルタリングパラメータ:" . implode(', ', $selectedFilters));

        // 現在のユーザーの選択**
        $personHopeJobTypesCode = DB::table('person_hope_job_type')
            ->where('staff_code', $person)
            ->pluck('job_type_code')
            ->toArray();

        $personHopeWorkingPlaces = DB::table('person_hope_working_place')
            ->where('staff_code', $person)
            ->pluck('prefecture_code')
            ->toArray();

        $personHopeWorkingCondition = DB::table('person_hope_working_condition')
            ->where('staff_code', $person)
            ->select('hourly_income_min', 'yearly_income_min')
            ->first();

        if (empty($personHopeJobTypesCode) || empty($personHopeWorkingPlaces) || !$personHopeWorkingCondition) {
            return response()->json([
                'jobs_html' => '',
                'total_jobs' => 0
            ]);
        }
        $selectedFlags = $request->input('supplement_flags', []);
        $checkboxOptions = $this->checkboxOptions();
        Log::info("📌 選択された補足フラグ:", $selectedFlags);

        DB::enableQueryLog();
        // 🔹 一致する求人を取得する（showMatch メソッドに基づく）
        $matchingJobs = DB::table('job_order')
            ->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
            ->join('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
            ->leftJoin('job_license', 'job_order.order_code', '=', 'job_license.order_code')
            ->join('job_supplement_info', 'job_order.order_code', '=', 'job_supplement_info.order_code')
            ->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
            ->join('master_code', function ($join) {
                $join->on('master_code.code', '=', 'job_working_place.prefecture_code')
                    ->where('master_code.category_code', '=', 'Prefecture');
            })
            ->whereIn('job_job_type.job_type_code', $personHopeJobTypesCode)
            ->whereIn('job_working_place.prefecture_code', $personHopeWorkingPlaces)
            ->when($personHopeWorkingCondition->yearly_income_min, function ($query) use ($personHopeWorkingCondition) {
                return $query->where('job_order.yearly_income_min', '>=', $personHopeWorkingCondition->yearly_income_min);
            })
            ->when($personHopeWorkingCondition->hourly_income_min, function ($query) use ($personHopeWorkingCondition) {
                return $query->where('job_order.hourly_income_min', '>=', $personHopeWorkingCondition->hourly_income_min);
            })
            ->when(!empty($selectedFilters), function ($query) use ($selectedFilters) {
                foreach ($selectedFilters as $filter) {
                    $query->where("job_supplement_info.{$filter}", 1);
                }
            })
            ->where('job_order.public_flag', '=', 1)
            ->where('job_order.order_progress_type', '=', 1)
            ->where('job_order.public_limit_day', '>=', now())
            ->select([
                'job_order.order_code as id',
                'job_supplement_info.pr_title1',
                'job_supplement_info.pr_contents1',
                'job_order.job_type_detail',
                'master_company.company_name_k',
                'job_order.yearly_income_min',
                'job_order.yearly_income_max',
                'job_order.hourly_income_min',
                'job_order.hourly_income_max',
                'master_code.detail as prefecture_name',
                'job_order.update_at',
            ])
            ->addSelect(array_keys($checkboxOptions))
            ->groupBy(
                'job_order.order_code',
                'job_order.job_type_detail',
                'job_order.yearly_income_min',
                'job_order.yearly_income_max',
                'job_order.hourly_income_min',
                'job_order.hourly_income_max',
                'master_company.company_name_k',
                'master_code.detail',
                'job_working_place.city',
                'job_working_place.town',
                'job_job_type.job_type_code',
                'job_supplement_info.pr_title1',
                'job_supplement_info.pr_contents1',
            )
            ->orderBy('job_order.update_at', 'desc')
            ->distinct()
            ->paginate(6);

        Log::info("📌  フィルタリングされた結果の数:" . $matchingJobs->total());
        $checkboxOptions = $this->checkboxOptions();
        foreach ($matchingJobs as $job) {
            $job->selectedFlagsArray = [];
            foreach ($checkboxOptions as $key => $label) {
                if (!empty($job->$key) && $job->$key == 1) {
                    $job->selectedFlagsArray[] = $key;
                }
            }
        }
        foreach ($matchingJobs as $result) {
            $result->yearly_income_display = "{$result->yearly_income_min}円〜" . ($result->yearly_income_max > 0 ? "{$result->yearly_income_max}円" : '');
            $result->hourly_income_display = "{$result->hourly_income_min}円〜" . ($result->hourly_income_max > 0 ? "{$result->hourly_income_max}円" : '');
        }

        if ($matchingJobs && count($matchingJobs) > 0) {
            //dd($matchingJobs->total());
            $matchCount = $matchingJobs->total(); //count($matchingJobs);
            DB::table('log_person_signin')
                ->updateOrInsert(
                    ['staff_code' => $person],
                    fn($exists) => $exists ? [
                        'staff_code' => $person,
                        'update_count' => $matchCount,
                        'update_at' => now(),
                    ] : [
                        'staff_code' => $person,
                        'update_count' =>  $matchCount,
                        'created_at' => now(),
                        'update_at' => now(),
                    ],
                );
        }

        // 🔹 AJAXの求人情報を返します
        $jobsHtml = view('partials.jobs', compact('matchingJobs'))->render();
        return response()->json([
            'jobs_html' => view('partials.jobs', compact('matchingJobs', 'checkboxOptions'))->render(),
            'pagination_html' => $matchingJobs->appends(['filters' => $selectedFilters])->links('vendor.pagination.default')->render(),
            'total_jobs' => $matchingJobs->total()
        ]);
    }
}
