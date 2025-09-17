<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\MasterPerson;
use App\Models\PersonUserInfo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\PersonHopeWorkingCondition;
use Carbon\Carbon;

class BasicInfoController extends Controller
{
    public function getAddressFromZipcloud(Request $request)
    {
        Log::info('郵便番号検索開始', $request->all());

        $zipcode = $request->post_u . $request->post_l;

        if (strlen($zipcode) !== 7) {
            Log::warning('郵便番号が7桁ではありません: ' . $zipcode);
            return response()->json(['error' => '郵便番号は7桁である必要があります。'], 400);
        }

        $response = Http::get("https://zipcloud.ibsnet.co.jp/api/search?zipcode=" . $zipcode);

        if ($response->successful()) {
            $data = $response->json();

            if (!empty($data['results'])) {
                $city = $data['results'][0]['address2'];
                $town = $data['results'][0]['address3'];
                Log::info('郵便番号から住所取得成功', ['city' => $city, 'town' => $town]);
                return response()->json([
                    'city' => $city,
                    'town' => $town,
                    'full_address' => $data['results'][0]['address1'] . ' ' . $city . ' ' . $town
                ]);
            }
        }

        Log::error('住所取得失敗: ' . $zipcode);
        return response()->json(['error' => '郵便番号が見つかりませんでした。'], 404);
    }

    public function showRegisterForm()
    {
        Log::info('基本情報登録フォーム表示');

        $bigClasses = DB::table('master_job_type')
            ->select('big_class_code', 'big_class_name')
            ->distinct()
            ->get();

        $prefectures = DB::table('master_code')
            ->where('category_code', 'Prefecture')
            ->get();

        return view('register', compact('bigClasses', 'prefectures'));
    }

    public function store(Request $request)
    {
        Log::info('基本情報登録処理開始', $request->all());

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'name_f' => 'required|string|max:255',
            'mail_address' => 'required|email|unique:master_person,mail_address',
            'password' => 'required|string|min:3',
            'phone_number' => 'required|string',
            'birthday' => 'required|digits:8',
            'post_u' => 'required|size:3',
            'post_l' => 'required|size:4',
            'gender' => 'required|in:1,2',
            'big_class_code' => 'required|string',
            'middle_class_code' => 'required|string',
            'prefecture_code' => 'required|array',
        ]);

        $zipcode = $request->post_u . $request->post_l;
        Log::info('郵便番号から都道府県取得開始: ' . $zipcode);
        $response = Http::get("https://zipcloud.ibsnet.co.jp/api/search?zipcode=" . $zipcode);

        $prefecture = '';
        $city = '';
        $town = '';
        $prefectureCode = null;

        if ($response->successful() && !empty($response['results'][0])) {
            $prefecture = $response['results'][0]['address1'];
            $city = $response['results'][0]['address2'];
            $town = $response['results'][0]['address3'];

            Log::info('都道府県取得成功', compact('prefecture', 'city', 'town'));

            $prefectureCode = DB::table('master_code')
                ->where('category_code', 'Prefecture')
                ->where('detail', $prefecture)
                ->value('code');
        }

        if (!$city || !$prefectureCode) {
            Log::error('住所情報不足', compact('city', 'prefectureCode'));
            return back()->withErrors(['zipcode' => '郵便番号から住所が取得できませんでした。'])->withInput();
        }

        try {
            DB::beginTransaction();

            $lastId = DB::table('master_person')->max(DB::raw("CAST(SUBSTRING(staff_code, 2) AS UNSIGNED)"));
            $nextId = $lastId ? $lastId + 1 : 1;
            $staffCode = 'S' . str_pad($nextId, 7, '0', STR_PAD_LEFT);
            Log::info("新しいスタッフコード生成: $staffCode");

            $person = MasterPerson::create([
                'staff_code' => $staffCode,
                'name' => $request->name,
                'name_f' => $request->name_f,
                'mail_address' => $request->mail_address,
                'portable_telephone_number' => $request->phone_number,
                'sex' => $request->gender,
                'birthday' => Carbon::createFromFormat('Ymd', $request->birthday)->format('Y-m-d'),
                'age' => Carbon::parse($request->birthday)->age,
                'post_u' => $request->post_u,
                'post_l' => $request->post_l,
                'prefecture_code' => $prefectureCode,
                'city' => $city,
                'town' => $town,
                'address' => $request->address,
                'city_f' => $request->city_f,
                'town_f' => $request->town_f,
                'address_f' => $request->address_f,
                'regist_commit' => 1,
            ]);

            Log::info("マスタ登録成功: {$person->staff_code}");

            PersonUserInfo::updateOrInsert(
                ['staff_code' => $staffCode],
                ['password' => strtoupper(md5($request->password)), 'regist_commit' => 1]
            );
            Log::info("ユーザー情報登録成功: {$staffCode}");
            $maxId = DB::table('person_hope_job_type')
                ->where('staff_code', $person)
                ->max('id');

            $newId = $maxId ? $maxId + 1 : 1;
            $jobTypeDetail = DB::table('master_job_type')
                ->where('big_class_code', $request->big_class_code)
                ->where('middle_class_code', $request->middle_class_code)
                ->value('middle_clas_name');

            Log::info('取得した職種詳細（job_type_detail）', [
                'big_class_code' => $request->big_class_code,
                'middle_class_code' => $request->middle_class_code,
                'job_type_detail' => $jobTypeDetail
            ]);

            $jobTypeCode = $request->big_class_code . $request->middle_class_code . '000';
            DB::table('person_hope_job_type')->insert([
                'staff_code' => $staffCode,
                'id' => $newId++,
                'job_type_code' => $jobTypeCode,
                'job_type_detail' => $jobTypeDetail,
                'created_at' => now(),
                'update_at' => now(),
            ]);
            Log::info("希望職種登録成功: {$jobTypeCode}");

            $maxId = DB::table('person_hope_working_place')
                ->where('staff_code', $staffCode)
                ->max('id');

            $nextId = $maxId ? $maxId + 1 : 1;

            foreach ($request->prefecture_code as $code) {
                DB::table('person_hope_working_place')->insert([
                    'staff_code' => $staffCode,
                    'id' => $nextId++,
                    'prefecture_code' => $code,
                    'city' => $city,
                    'area' => '日本',
                    'created_at' => now(),
                    'update_at' => now(),
                ]);
                Log::info("勤務地登録: {$code}");
            }

            PersonHopeWorkingCondition::updateSelectiveOrCreate($staffCode, [
                'yearly_income_min' => $request->desired_salary_type === '年収' ? $request->desired_salary_annual * 10000 : 0,
                'hourly_income_min' => $request->desired_salary_type === '時給' ? $request->desired_salary_hourly : 0,
            ]);
            Log::info("希望給与登録完了: {$staffCode}");

            DB::commit();

            try {
                Mail::to($person->mail_address)->send(new VerifyEmail($person));
                Log::info("確認メール送信成功: {$person->mail_address}");
            } catch (\Exception $e) {
                Log::error("メール送信エラー: " . $e->getMessage());
            }

            Auth::login($person);
            Log::info("ログイン成功: {$staffCode}");

            return redirect()->route('educate-history')->with('success', '基本情報登録されました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('登録エラー: ' . $e->getMessage());
            return back()->withErrors(['error' => '登録に失敗しました。もう一度試してください。'])->withInput();
        }
    }
}
