<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterPerson;
use App\Models\PersonHopeWorkingCondition;
use App\Models\PersonHopeJobType;
use App\Models\PersonUserInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use stdClass;

class MypageController extends Controller
{
	public function myPage()
	{
		$person = Auth::user();
		if (!$person) {
			return redirect()->route('login')->withErrors('ログインしてください。');
		}

		// マッチングをチェックする
		$staffCode = $person->staff_code;
		Log::info("📌 STAFF_CODE:", ['staff_code' => $staffCode]);

		$hasWorkingCondition = DB::table('person_hope_working_condition')
			->where('staff_code', $staffCode)
			->exists();

		$hasWorkingPlace = DB::table('person_hope_working_place')
			->where('staff_code', $staffCode)
			->exists();

		$hasJobType = DB::table('person_hope_job_type')
			->where('staff_code', $staffCode)
			->exists();
		$hasPersonLicense = DB::table('person_license')
			->where('staff_code', $staffCode)
			->select('group_code', 'category_code', 'code')
			->get()
			->toArray();

		$hasMatching = $hasWorkingCondition && $hasWorkingPlace && $hasJobType && $hasPersonLicense;



		// ユーザーデータの可用性を確認する
		$hasWorkingCondition = DB::table('person_hope_working_condition')
			->where('staff_code', $staffCode)
			->select('yearly_income_min', 'hourly_income_min')
			->first();
		Log::info("📌 User's Working Condition:", (array) $hasWorkingCondition);


		$desiredSalaryType = null;
		if ($hasWorkingCondition) {
			if (!is_null($hasWorkingCondition->yearly_income_min)) {
				$desiredSalaryType = '年収';
			} elseif (!is_null($hasWorkingCondition->hourly_income_min)) {
				$desiredSalaryType = '時給';
			}
		}
		Log::info("✅ Selected Salary Type:", ['desiredSalaryType' => $desiredSalaryType]);

		// ユーザーの労働条件
		$personHopeWorkingCondition = DB::table('person_hope_working_condition')
			->where('staff_code', $staffCode)
			->select('hourly_income_min', 'yearly_income_min')
			->first();

		// 求人タイプと場所のフィルター
		$personHopeJobTypesCode = DB::table('person_hope_job_type')
			->where('staff_code', $staffCode)
			->pluck('job_type_code')
			->toArray();
		Log::info("📌 User's Preferred Job Types:", $personHopeJobTypesCode);

		$personHopeWorkingPlaces = DB::table('person_hope_working_place')
			->where('staff_code', $staffCode)
			->pluck('prefecture_code')
			->toArray();
		Log::info("📌 User's Preferred Working Places:", $personHopeWorkingPlaces);

		$personLicense = DB::table('person_license')
			->where('staff_code', $staffCode)
			->select('group_code', 'category_code', 'code')
			->get()
			->toArray();
		Log::info("📌 User's Licenses:", $personLicense);
		$groupCodes = array_column($personLicense, 'group_code');
		$categoryCodes = array_column($personLicense, 'category_code');
		$codes = array_column($personLicense, 'code');

		// 🎯 炭素による年齢計算
		$personBirthDay = DB::table('master_person')
			->where('staff_code', $staffCode)
			->select('birthday')
			->first();
		Log::info("📌 User's Birthday:", (array) $personBirthDay);

		$personAge = $personBirthDay ? Carbon::parse($personBirthDay->birthday)->age : null;
		Log::info("📌 Calculated Age:", ['age' => $personAge]);

		// マッチングする仕事を獲得する
		DB::enableQueryLog();
		Log::info("🔍 Starting Query Execution...");

		// ユーザーに一致する求人の数を決定します (`showMatchingResults` と同じフィルター)**
		$matchingJobCount = DB::table('job_order')
			->join('job_job_type', 'job_order.order_code', '=', 'job_job_type.order_code')
			->join('job_working_place', 'job_order.order_code', '=', 'job_working_place.order_code')
			->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
			->leftJoin('job_license', 'job_order.order_code', '=', 'job_license.order_code')
			->when(!empty($personHopeJobTypesCode), function ($query) use ($personHopeJobTypesCode) {
				return $query->whereIn('job_job_type.job_type_code', $personHopeJobTypesCode);
			})
			->when(!empty($personHopeWorkingPlaces), function ($query) use ($personHopeWorkingPlaces) {
				return $query->whereIn('job_working_place.prefecture_code', $personHopeWorkingPlaces);
			})

			->when($desiredSalaryType === '時給' && !is_null($personHopeWorkingCondition), function ($query) use ($personHopeWorkingCondition) {
				return $query->where('job_order.hourly_income_min', '>=', $personHopeWorkingCondition->hourly_income_min);
			})
			->when($desiredSalaryType === '年収' && !is_null($personHopeWorkingCondition), function ($query) use ($personHopeWorkingCondition) {
				return $query->where('job_order.yearly_income_min', '>=', $personHopeWorkingCondition->yearly_income_min);
			})

			->where(function ($query) use ($personAge) {
				$query->where('job_order.age_max', '>=', $personAge)
					->orWhere('job_order.age_max', '=', '0');
			})
			// ->when(!empty($groupCodes) && !empty($categoryCodes) && !empty($codes), function ($query) use ($groupCodes, $categoryCodes, $codes) {
			// 	return $query->where(function ($subQuery) use ($groupCodes, $categoryCodes, $codes) {
			// 		$subQuery->whereIn('job_license.group_code', $groupCodes)
			// 			->whereIn('job_license.category_code', $categoryCodes)
			// 			->whereIn('job_license.code', $codes);
			// 	});
			// }) // ✅ person_license データはオプションの状態で比較されました
			->where('job_order.public_flag', '=', 1)
			->where('job_order.order_progress_type', '=', 1)
			->where('job_order.public_limit_day', '>=', now())
			->orderBy('job_order.update_at', 'desc')
			->distinct()
			->count();

		Log::info('Query Log:', DB::getQueryLog());
		Log::info("🔍 Salary Filtering Applied:", [
			'salary_type' => $desiredSalaryType,
			'hourly_income_min' => $personHopeWorkingCondition->hourly_income_min ?? 'null',
			'yearly_income_min' => $personHopeWorkingCondition->yearly_income_min ?? 'null'
		]);
		$viewedJobs = DB::table('log_access_history_staff')
			->join('job_order', 'log_access_history_staff.order_code', '=', 'job_order.order_code')
			->join('master_company', 'job_order.company_code', '=', 'master_company.company_code')
			->where('log_access_history_staff.staff_code', $staffCode)
			->orderByDesc('log_access_history_staff.update_at')
			->select(
				'log_access_history_staff.order_code',
				'log_access_history_staff.update_at',
				'job_order.job_type_detail',
				'job_order.order_type',
				'master_company.company_name_k'
			)
			->paginate(5); // ← sahifasiga 10 ta

		//print_r($companyName);
		//dd($jobs);

		// すべての条件（希望分システム登限）が完全に登録されているかどうかを確認します。
		$hasMatching = $hasWorkingCondition && $hasWorkingPlace && $hasJobType;

		// `希望的登録条件` が存在し、情報がある場合にのみ `$matchingJobCount` を出力します。
		if (!$hasMatching || is_null($matchingJobCount) || $matchingJobCount == 0) {
			$matchingJobCount = null; // 結果がない場合は、Blade に表示されないように `null` に設定します。
		}
		Log::info('📌 Salary Formatting Completed');
		Log::info("🔍 Mypage Matching Jobs Count:", ['count' => $matchingJobCount]);
		DB::table('log_person_signin')
			->updateOrInsert(
				['staff_code' => $staffCode],
				fn($exists) => $exists ? [
					'staff_code' => $staffCode,
					'mypage' => DB::raw('mypage+1'),
					'mypage_at' => now(),
					'update_at' => now(),
				] : [
					'staff_code' => $staffCode,
					'mypage' =>  1,
					'mypage_at' => now(),
					'created_at' => now(),
					'update_at' => now(),
				],
			);
		//dd(DB::getQueryLog());

		return view('mypage', compact('hasMatching',     'matchingJobCount', 'viewedJobs'));
	}
	public function resume(Request $request)
	{
		return view('resume');
	}

	public function showEducateStoryForm()
	{
		DB::enableQueryLog();

		$person = Auth::user();
		if (!$person) {
			return redirect()->route('login')->withErrors('ログインしてください。');
		}

		$staffCode = $person->staff_code;

		// Master data queries
		$graduateTypes = DB::table('master_code')->where('category_code', 'GraduateType')->get();
		$entryTypes = DB::table('master_code')->where('category_code', 'EntryType')->get();
		$courseTypes = DB::table('master_code')->where('category_code', 'CourseType')->get();
		$schoolTypes = DB::table('master_code')->where('category_code', 'SchoolType')->get();
		$industryTypes = DB::table('master_code')->where('category_code', 'IndustryTypeDsp')->get();
		$workingTypes = DB::table('master_code')->where('category_code', 'WorkingType')->get();
		$jobTypes = DB::table('master_job_type')->get();

		// 教育履歴を取得する
		$schools = DB::table('person_educate_history as his')
			->join('master_code as mcd', function ($join) {
				$join->on('mcd.code', '=', 'his.school_type_code')
					->where('mcd.category_code', '=', 'SchoolType');
			})
			->leftJoin('master_code as mc2', function ($join) {
				$join->on('mc2.code', '=', 'his.graduate_type_code')
					->where('mc2.category_code', '=', 'GraduateType');
			})
			->leftJoin('master_code as mc3', function ($join) {
				$join->on('mc3.code', '=', 'his.course_type')
					->where('mc3.category_code', '=', 'CourseType');
			})
			->leftJoin('master_code as mc4', function ($join) {
				$join->on('mc4.code', '=', 'his.entry_type_code')
					->where('mc4.category_code', '=', 'EntryType');
			})
			->select(
				'his.id',
				'his.school_name',
				DB::raw('YEAR(his.entry_day) as entry_day_year'),
				DB::raw('IFNULL(LPAD(MONTH(his.entry_day), 2, "0"), "") as entry_day_month'),
				DB::raw('YEAR(his.graduate_day) as graduate_day_year'),
				DB::raw('IFNULL(LPAD(MONTH(his.graduate_day), 2, "0"), "") as graduate_day_month'),
				'his.speciality',
				'his.school_type_code',
				'mcd.detail as school_type',
				'his.graduate_type_code',
				'mc2.detail as graduate_type',
				'his.course_type',
				'mc3.detail as course',
				'his.entry_type_code',
				'mc4.detail as entry_type'
			)
			->where('staff_code', $staffCode)
			->get();

		// 経歴を取得する
		$careers = DB::table('person_career_history as pch')
			->leftJoin('master_job_type as mj', 'pch.job_type_code', '=', 'mj.all_connect_code')
			->leftJoin('master_code as mcd', function ($join) {
				$join->on('mcd.code', '=', 'pch.industry_type_code')
					->where('mcd.category_code', '=', 'IndustryTypeDsp');
			})
			->leftJoin('master_code as mc2', function ($join) {
				$join->on('mc2.code', '=', 'pch.working_type_code')
					->where('mc2.category_code', '=', 'WorkingType');
			})
			->select(
				'pch.staff_code',
				'pch.id',
				'pch.job_type_detail',
				'pch.company_name',
				'pch.job_type_code',
				'pch.industry_type_code',
				'mcd.detail as industry_type',
				'pch.working_type_code',
				'mc2.detail as working_type',
				'mj.big_class_code',
				DB::raw('COALESCE(mj.big_class_code, "") as big_class_code'),
				'mj.big_class_name',
				'mj.middle_class_code',
				'mj.middle_clas_name',
				'mj.small_clas_code',
				'mj.small_class_name',
				'pch.capital',
				'pch.number_employees',
				'pch.business_detail',
				'pch.entry_day',
				'pch.retire_day',
				DB::raw('YEAR(pch.entry_day) as entry_day_year'),
				DB::raw('IFNULL(LPAD(MONTH(pch.entry_day), 2, "0"), "") as entry_day_month'),
				DB::raw('YEAR(pch.retire_day) as retire_day_year'),
				DB::raw('IFNULL(LPAD(MONTH(pch.retire_day), 2, "0"), "") as retire_day_month')
			)
			->where('pch.staff_code', $staffCode)
			->get();

		// `$careers`が空の場合は、空のオブジェクトを追加します
		if ($careers->isEmpty()) {
			$careers = collect([(object) [
				'company_name' => '',
				'capital' => 0,
				'number_employees' => 0,
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
			]]);
		}


		// すべてのデータを含むビューを返す
		return view('educate-history', compact(
			'graduateTypes',
			'entryTypes',
			'courseTypes',
			'schoolTypes',
			'industryTypes',
			'workingTypes',
			'jobTypes',
			'schools',
			'careers',
			'staffCode'
		));
	}

	public function storeEducateHistory(Request $request)
	{
		DB::enableQueryLog();
		// dd($request->all());
		$request->merge([
			'educations' => array_map(function ($education) {
				$education['entry_day_month'] = ($education['entry_day_month'] == '00') ? null : $education['entry_day_month'];
				$education['graduate_day_month'] = ($education['graduate_day_month'] == '00') ? null : $education['graduate_day_month'];
				return $education;
			}, $request->input('educations', []))
		]);
		$request->merge([
			'careers' => array_map(function ($career) {
				$career['entry_day_month'] = ($career['entry_day_month'] == '00') ? null : $career['entry_day_month'];
				$career['retire_day_month'] = ($career['retire_day_month'] == '00') ? null : $career['retire_day_month'];
				$career['number_employees'] = ($career['number_employees'] == '0') ? null : $career['number_employees'];
				return $career;
			}, $request->input('careers', []))
		]);


		$person = Auth::user();
		if (!$person) {
			return redirect()->route('login')->withErrors('ログインしてください。');
		}

		$staffCode = $person->staff_code;
		// 検証ルール
		$rules = [
			'educations.*.staff_code' => 'nullable|string|max:255',
			'educations.*.school_name' => 'nullable|string|max:255',
			'educations.*.school_type_code' => 'nullable|string|max:255',
			'educations.*.entry_day_year' => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
			'educations.*.entry_day_month' => 'nullable|numeric|min:1|max:12',
			'educations.*.graduate_day_year' => 'nullable|digits:4|integer|min:1900|max:' . (date('Y') + 10),

			'careers.*.graduate_day_month' => 'nullable|numeric|min:1|max:12',
			'careers.*.staff_code' => 'nullable|string|max:255',
			'careers.*.company_name' => 'nullable|string|max:255',
			'careers.*.capital' => 'nullable|integer|min:0',
			'careers.*.number_employees' => 'nullable|integer|min:1|exclude_if:careers.*.number_employees,0',
			'careers.*.entry_day_year' => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
			'careers.*.entry_day_month' => 'nullable|numeric|between:1,12|exclude_if:careers.*.entry_day_month,00',
			'careers.*.retire_day_year' => 'nullable|digits:4|integer|min:1900|max:' . (date('Y') + 10),
			'careers.*.retire_day_month' => 'nullable|numeric|between:1,12|exclude_if:careers.*.retire_day_month,00',
			// 'careers.*.currently_employed' => 'nullable|boolean',
			'careers.*.industry_type_code' => 'nullable|string|max:255',
			'careers.*.working_type_code' => 'nullable|string|max:255',
			'careers.*.job_type_big_code' => 'nullable|string|max:255',
			'careers.*.job_type_small_code' => 'nullable|string|max:255',
			'careers.*.job_type_detail' => 'nullable|string|max:1000',
			'careers.*.business_detail' => 'nullable|string|max:1000',
		];
		// dd($rules);
		$messages = [
			// Education validation messages
			'educations.*.staff_code.max' => '学歴のスタッフコードは255文字以内で入力してください。',
			'educations.*.school_name.max' => '学校名は255文字以内で入力してください。',
			'educations.*.school_type_code.max' => '学校タイプコードは255文字以内で入力してください。',
			'educations.*.entry_day_year.digits' => '入学日の年は4桁で入力してください。',
			'educations.*.entry_day_year.integer' => '入学日の年は整数で入力してください。',
			'educations.*.entry_day_year.min' => '入学日の年は1900年以降で入力してください。',
			'educations.*.entry_day_year.max' => '入学日の年は' . date('Y') . '年以前で入力してください。',
			'educations.*.entry_day_month.digits_between' => '入学日の月は1〜2桁で入力してください。',
			'educations.*.entry_day_month.integer' => '入学日の月は整数で入力してください。',
			'educations.*.entry_day_month.min' => '入学日の月は1以上で入力してください。',
			'educations.*.entry_day_month.max' => '入学日の月は12以下で入力してください。',
			'educations.*.graduate_day_year.digits' => '卒業日の年は4桁で入力してください。',
			'educations.*.graduate_day_year.integer' => '卒業日の年は整数で入力してください。',
			'educations.*.graduate_day_year.min' => '卒業日の年は1900年以降で入力してください。',
			'educations.*.graduate_day_year.max' => '卒業日の年は' . (date('Y') + 10) . '年以前で入力してください。',
			'educations.*.graduate_day_month.digits_between' => '卒業日の月は1〜2桁で入力してください。',
			'educations.*.graduate_day_month.integer' => '卒業日の月は整数で入力してください。',
			'educations.*.graduate_day_month.min' => '卒業日の月は1以上で入力してください。',
			'educations.*.graduate_day_month.max' => '卒業日の月は12以下で入力してください。',

			// Career validation messages
			'careers.*.staff_code.max' => '職歴のスタッフコードは255文字以内で入力してください。',
			'careers.*.company_name.max' => '会社名は255文字以内で入力してください。',
			'careers.*.capital.integer' => '資本金は整数で入力してください。',
			'careers.*.capital.min' => '資本金は0以上で入力してください。',
			'careers.*.number_employees.integer' => '従業員数は整数で入力してください。',
			'careers.*.number_employees.min' => '従業員数は1以上で入力してください。',
			'careers.*.entry_day_year.digits' => '入社日の年は4桁で入力してください。',
			'careers.*.entry_day_year.integer' => '入社日の年は整数で入力してください。',
			'careers.*.entry_day_year.min' => '入社日の年は1900年以降で入力してください。',
			'careers.*.entry_day_year.max' => '入社日の年は' . date('Y') . '年以前で入力してください。',
			'careers.*.entry_day_month.digits' => '入社日の月は2桁で入力してください。',
			'careers.*.entry_day_month.integer' => '入社日の月は整数で入力してください。',
			'careers.*.entry_day_month.min' => '入社日の月は01以上で入力してください。',
			'careers.*.entry_day_month.max' => '入社日の月は12以下で入力してください。',
			'careers.*.retire_day_year.digits' => '退社日の年は4桁で入力してください。',
			'careers.*.retire_day_year.integer' => '退社日の年は整数で入力してください。',
			'careers.*.retire_day_year.min' => '退社日の年は1900年以降で入力してください。',
			'careers.*.retire_day_year.max' => '退社日の年は' . (date('Y') + 10) . '年以前で入力してください。',
			'careers.*.retire_day_month.digits' => '退社日の月は2桁で入力してください。',
			'careers.*.retire_day_month.integer' => '退社日の月は整数で入力してください。',
			'careers.*.retire_day_month.min' => '退社日の月は01以上で入力してください。',
			'careers.*.retire_day_month.max' => '退社日の月は12以下で入力してください。',
			'careers.*.industry_type_code.max' => '業種コードは255文字以内で入力してください。',
			'careers.*.working_type_code.max' => '勤務形態コードは255文字以内で入力してください。',
			'careers.*.job_type_big_code.max' => '職種大分類コードは255文字以内で入力してください。',
			'careers.*.job_type_small_code.max' => '職種小分類コードは255文字以内で入力してください。',
			'careers.*.job_type_detail.max' => '職種の詳細は1000文字以内で入力してください。',
			'careers.*.business_detail.max' => '職務内容は1000文字以内で入力してください。',
		];
		// 検証の実行
		// $validator = Validator::make($request->all(), $rules, $messages);
		// if ($validator->fails()) {
		// 	return redirect()->back()->withErrors($validator)->withInput();
		// }
		$validator = Validator::make($request->all(), $rules, $messages);
		if ($validator->fails()) {
			return redirect()->back()->withErrors($validator)->withInput(); // ← bu muhim
		}

		// 検証が成功した場合は続行されます。
		$validatedData = $validator->validated();
		DB::enableQueryLog();
		// 受信データの受信
		$educations = $request->input('educations', []);
		$careers = $request->input('careers', []);

		DB::beginTransaction();
		try {
			$educationUpdateData = [];
			$educationInsertData = [];
			$idsToUpdate = [];

			// ✅ 1️⃣ 教育データストレージ
			$educations = $request->input('educations', []);
			foreach ($educations as $education) {
				$entryDay = (!empty($education['entry_day_year']) && !empty($education['entry_day_month']))
					? "{$education['entry_day_year']}-" . sprintf('%02d', $education['entry_day_month']) . "-01"
					: '0000-00-00 00:00:00';

				$graduateDay = (!empty($education['graduate_day_year']) && !empty($education['graduate_day_month']))
					? "{$education['graduate_day_year']}-" . sprintf('%02d', $education['graduate_day_month']) . "-01"
					: '0000-00-00 00:00:00';

				$existingRecord = DB::table('person_educate_history')
					->where('staff_code', $staffCode)
					->where('id', $education['id'] ?? 0) // IDで検索
					->first();

				if ($existingRecord) {
					$existingId = $existingRecord->id;
				} else {
					$existingId = null;
				}
				if ($existingId) {
					$affectedRows = DB::table('person_educate_history')
						->where('id', $existingId)
						->where('staff_code', $staffCode)
						->update([
							'school_name' => $education['school_name'],
							'school_type_code' => $education['school_type_code'],
							'speciality' => $education['speciality'] ?? null,
							'course_type' => $education['course_type'] ?? null,
							'entry_day' => $entryDay,
							'graduate_day' => $graduateDay,
							'entry_type_code' => $education['entry_type_code'] ?? null,
							'graduate_type_code' => $education['graduate_type_code'] ?? null,
							'update_at' => now(),
						]);

					if ($affectedRows === 0) {
						Log::warning("No update occurred", ['id' => $existingId, 'staff_code' => $staffCode]);
					} else {
						Log::info("Education updated", ['id' => $existingId, 'affectedRows' => $affectedRows]);
					}
				} else {
					// 🆕 新しいIDを取得して追加する
					$newId = DB::table('person_educate_history')
						->where('staff_code', $staffCode)
						->max('id');

					$newId = $newId ? ($newId + 1) : 1;


					DB::table('person_educate_history')->insert([
						'id' => $newId,
						'staff_code' => $staffCode,
						'school_name' => $education['school_name'],
						'school_type_code' => $education['school_type_code'],
						'speciality' => $education['speciality'] ?? null,
						'course_type' => $education['course_type'] ?? null,
						'entry_day' => $entryDay,
						'graduate_day' => $graduateDay,
						'entry_type_code' => $education['entry_type_code'] ?? null,
						'graduate_type_code' => $education['graduate_type_code'] ?? null,
						'created_at' => now(),
						'update_at' => now(),
					]);
					Log::info("New education inserted", ['id' => $newId]);
				}
			}

			// ✅ 2️⃣キャリア情報の維持
			$careers = $request->input('careers', []);
			foreach ($careers as $career) {
				$entryDay = (!empty($career['entry_day_year']) && !empty($career['entry_day_month']))
					? "{$career['entry_day_year']}-" . sprintf('%02d', (int)$career['entry_day_month']) . "-01"
					: '0000-00-00 00:00:00';

				$retireDay = (!empty($career['retire_day_year']) && !empty($career['retire_day_month']))
					? "{$career['retire_day_year']}-" . sprintf('%02d', (int)$career['retire_day_month']) . "-01"
					: '0000-00-00 00:00:00';
				// ✅ ID orqali mavjudligini tekshirish
				$existingRecord = DB::table('person_career_history')
					->where('staff_code', $staffCode)
					->where('id', $career['id'] ?? 0)
					->first();

				// Foydalanuvchidan kelyapti (万円 birligida), bo‘sh bo‘lsa 0
				$capital = isset($career['capital']) && $career['capital'] !== '' ? (int)$career['capital'] : 0;

				// 1000 ga ko‘paytirish: agar 1〜999 oralig‘ida bo‘lsa
				$convertedCapital = ($capital > 0 && $capital < 10000) ? $capital * 10000 : $capital;

				// $existingId = DB::table('person_career_history')
				// 	->where('staff_code', $staffCode)
				// 	->where('company_name', $career['company_name'])
				// 	->value('id');

				if ($existingRecord) {
					DB::table('person_career_history')
						->where('id', $existingRecord->id)
						->where('staff_code', $staffCode)
						->update([
							'company_name' => $career['company_name'],
							'capital' => $convertedCapital ?? 0,
							'number_employees' => $career['number_employees'] ?? null,
							'entry_day' => $entryDay,
							'retire_day' => $retireDay,
							'job_type_code' => ($career['job_type_big_code'] ?? '') . ($career['job_type_small_code'] ?? '') . '000',
							'industry_type_code' => $career['industry_type_code'] ?? null,
							'working_type_code' => $career['working_type_code'] ?? null,
							'job_type_detail' => $career['job_type_detail'] ?? null,
							'business_detail' => $career['business_detail'] ?? null,
							'update_at' => now(),
						]);

					Log::info("Career updated", ['id' => $existingRecord->id]);
				} else {
					// 🆕 新しいIDを取得して追加する
					// ✅ `staff_code` のみに基づいて新しい ID を取得します
					$newId = DB::table('person_career_history')
						->where('staff_code', $staffCode)
						->max('id') + 1 ?? 1;

					DB::table('person_career_history')->insert([
						'id' => $newId,
						'staff_code' => $staffCode,
						'company_name' => $career['company_name'] ?? '',
						'capital' => $convertedCapital ?? 0,
						'number_employees' => $career['number_employees'] ?? null,
						'entry_day' => $entryDay,
						'retire_day' => $retireDay,
						'job_type_code' => ($career['job_type_big_code'] ?? '') . ($career['job_type_small_code'] ?? '') . '000',
						'industry_type_code' => $career['industry_type_code'] ?? null,
						'working_type_code' => $career['working_type_code'] ?? null,
						'job_type_detail' => $career['job_type_detail'] ?? null,
						'business_detail' => $career['business_detail'] ?? null,
						'created_atday' => now(),
						'update_at' => now(),
					]);
					Log::info("New career inserted", ['id' => $newId]);
					Log::info('💡 Request Careers:', $request->input('careers', []));
				}
			}

			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			Log::error('DB Error: ' . $e->getMessage());
			return redirect()->back()->withErrors('エラーが発生しました。: ' . $e->getMessage());
		}

		Log::info('Education Insert:', $educationInsertData);
		Log::info('Career Insert:', $careers);
		Log::info('Education Update:', $educationUpdateData);
		Log::info('Career Update:', $careers);


		// Log::error($e->getMessage());
		// dd($entryDay, $graduateDay);

		//dd($educations, $careers);

		//dd($request->all());
		// 保存成功の報告
		$jobId = session('apply_job');
		if ($jobId) {
			session()->put('apply_job', $jobId);
			session()->save();  // ✅ セッションを強制保存
		}

		return redirect()
			->route('self_pr')
			->with('success', '学歴と職歴情報が保存されました。')
			->withInput();
	}
	public function destroyEducation($id)
	{
		$person = Auth::user();
		if (!$person) {
			return redirect()->route('login')->withErrors('ログインしてください。');
		}

		$staffCode = $person->staff_code;
		if (!$staffCode) {
			return redirect()->route('mypage')->withErrors('スタッフコードが見つかりません。');
		}

		Log::info("🔍 削除リクエストID: $id, Staff Code: {$staffCode}");

		$deleted = DB::table('person_educate_history')
			->where('id', $id)
			->where('staff_code', $staffCode)
			->delete();

		if ($deleted) {
			Log::info("✅ Successfully deleted education ID: $id");
		} else {
			Log::warning("⚠️ Failed to delete education ID: $id (record not found or mismatched staff_code)");
		}
		$existing = DB::table('person_educate_history')
			->where('id', $id)
			->first();

		if (!$existing) {
			Log::warning("⚠️ IDが利用できません: $id");
		}

		return response()->json(['success' => $deleted > 0]);
	}

	public function destroyCareer($id)
	{
		$user = auth()->user();
		$deleted = DB::table('person_career_history')
			->where('id', $id)
			->where('staff_code', $user->staff_code)
			->delete();

		return response()->json(['success' => $deleted > 0]);
	}
	public function showPRStoryForm()
	{
		$person = Auth::user();
		if (!$person) {
			return redirect()->route('login')->withErrors('ログインしてください。');
		}

		$staffCode = $person->staff_code;
		if (!$staffCode) {
			return redirect()->route('mypage')->withErrors('スタッフコードが見つかりません。');
		}

		// ユーザーの個人情報
		$personDetails = DB::table('master_person')
			->where('staff_code', $staffCode)
			->select('marriage_flag', 'dependent_number', 'dependent_flag')
			->first() ?? new stdClass();

		// 🟢 ユーザーの`self_pr`情報を取得する
		$selfPR = DB::table('person_self_pr')
			->where('staff_code', $staffCode)
			->select('self_pr')
			->first(); // 新しいstdClass(); // 利用できない場合は空のオブジェクト

		// すべてのグループを取得
		$groups = DB::table('master_license')
			->select('group_code', 'group_name')
			->distinct()
			->get();

		// ユーザーが保存したライセンス (ID で並べ替え)
		$licenses = DB::table('person_license as pl')
			->join('master_license as ml', function ($join) {
				$join->on('pl.group_code', '=', 'ml.group_code')
					->on('pl.category_code', '=', 'ml.category_code');
			})
			->where('pl.staff_code', $staffCode)
			->orderBy('pl.id') // ✅ IDで並べ替え
			->select('pl.id', 'pl.group_code', 'pl.category_code', 'pl.code', 'pl.get_day', 'ml.category_name', 'ml.name')
			->get()
			->toArray(); // 空の場合は正確な配列になります**

		$educations = DB::table('person_resume_other')
			->where('staff_code', $staffCode)
			->get()
			->toArray();
		// 🟢 **必要なカテゴリーのみ登録してください**
		$categories = DB::table('master_code')
			->whereIn('category_code', ['OS', 'Application', 'DevelopmentLanguage', 'Database'])
			->select('category_code')
			->distinct()
			->pluck('category_code', 'category_code')
			->toArray();

		// 🟢 各 `category_code` の `master_code` テーブルからスキルを取得します。
		$skills = DB::table('master_code')
			->whereIn('category_code', array_keys($categories))
			->select('category_code', 'code', 'detail')
			->get()
			->groupBy('category_code');

		// 🟢 ユーザーが以前に選択したスキルコードを取得する
		$selectedSkills = DB::table('person_skill')
			->where('staff_code', $staffCode)
			->pluck('code', 'category_code')
			->toArray();

		// ブレードに送信
		return view('self_pr', compact(
			'personDetails',
			'selfPR',
			'groups',
			'licenses',
			'educations',
			'skills',
			'selectedSkills',
			'categories'
		));
	}
	public function destroyLicense($id)
	{
		$person = Auth::user();
		if (!$person) {
			Log::warning("[資格削除] ユーザーが未認証です。");
			return response()->json(['success' => false, 'message' => 'ログインしてください。']);
		}

		$staffCode = $person->staff_code;
		Log::info("[資格削除] リクエスト ID={$id}, StaffCode={$staffCode}");

		$deleted = DB::table('person_license')
			->where('id', $id)
			->where('staff_code', $staffCode)
			->delete();

		if ($deleted) {
			Log::info("[資格削除] 成功: ID={$id} が StaffCode={$staffCode} から削除されました。");
		} else {
			Log::warning("[資格削除] 失敗: ID={$id} は StaffCode={$staffCode} に存在しませんでした。");
		}

		return response()->json(['success' => $deleted > 0]);
	}
	public function storePR(Request $request)
	{
		DB::enableQueryLog();
		Log::info("🔹 storePR() メソッドが呼び出されました.");

		$person = Auth::user();
		if (!$person) {
			Log::warning("⚠️ ユーザーが識別されません。ログインページへのリダイレクト.");
			return redirect()->route('login')->withErrors('ログインしてください。');
		}

		$staffCode = $person->staff_code;
		if (!$staffCode) {
			Log::warning("⚠️ ユーザー「staff_code」が見つかりません.");
			return redirect()->route('self_pr')->withErrors('スタッフコードが見つかりません。');
		}

		Log::info("✅ ユーザーが特定されました", ['staff_code' => $staffCode]);
		$request->merge([
			'marriage_flag' => is_numeric($request->marriage_flag) ? (int)$request->marriage_flag : null,
			'dependent_flag' => is_numeric($request->dependent_flag) ? (int)$request->dependent_flag : null,
		]);

		// フォームチェック
		$request->validate([
			'CONF_SelfPR' => 'nullable|string|max:2000',
			'marriage_flag' => 'nullable|integer|in:0,1',
			'dependent_number' => 'nullable|integer|min:0',
			'dependent_flag' => 'nullable|integer|in:0,1',
			'skills' => 'nullable|array',
			'skills.*' => 'nullable|array',
			'skills.*.*' => 'nullable|exists:master_code,code',
			'educations' => 'nullable|array',
		]);

		try {
			DB::beginTransaction();
			Log::info("🛠 取引が開始されます。.");

			// person_self_pr テーブルにデータを追加または更新する
			DB::table('person_self_pr')->updateOrInsert(
				['staff_code' => $staffCode],
				[
					'self_pr' => $request->input('CONF_SelfPR'),
					'created_at' => now(),
					'update_at' => now(),
				]
			);
			Log::info("✅ person_self_pr テーブルに情報が追加または更新されました。.");

			// master_person 更新
			DB::table('master_person')->updateOrInsert(
				['staff_code' => $staffCode],
				[
					'marriage_flag' => $request->input('marriage_flag'),
					'dependent_number' => $request->input('dependent_number'),
					'dependent_flag' => $request->input('dependent_flag'),
					'updated_at' => now(),
				]
			);
			Log::info("✅ master_person テーブルが更新されました。");

			// person_resume_other 更新
			if (!empty($request->educations)) {
				Log::info("🎓教育情報を保存しています... ");
				foreach ($request->educations as $education) {
					if (empty($education['subject'])) {
						continue;
					}

					DB::table('person_resume_other')->updateOrInsert(
						[
							'staff_code' => $staffCode,
							'id' => '1',
							'subject' => $education['subject'],
						],
						[
							'wish_motive' => $education['wish_motive'],
							'hope_column' => $education['hope_column'],
							'commute_time' => $education['commute_time'],
							'created_at' => now(),
							'update_at' => now(),
						]
					);
				}
				Log::info("✅ person_resume_other テーブルが更新されました。");
			}

			// ユーザーの「person_skill」情報を更新する
			if (!empty($request->skills)) {
				Log::info("💡 スキルが更新されています。");

				// 古いデータを削除する
				DB::table('person_skill')->where('staff_code', $staffCode)->delete();
				Log::info("🗑 古いスキルデータは削除されました。");

				$lastId = DB::table('person_skill')
					->where('staff_code', $staffCode)
					->max('id') ?? 0;

				foreach ($request->skills as $categoryCode => $skills) {
					foreach ($skills as $skillCode) {
						DB::table('person_skill')->insert([
							'staff_code' => $staffCode,
							'id' => ++$lastId,
							'category_code' => $categoryCode,
							'code' => $skillCode,
							'period' => 0,
							'start_day' => now(),
							'created_at' => now(),
							'update_at' => now(),
						]);
					}
				}
				Log::info("✅ person_skill テーブルが更新されました.");
			}

			// ライセンス情報を保存する
			if (!empty($request->licenses)) {
				Log::info("📜 ライセンスを保存しています...");

				// 古いデータを削除する（新しいデータが利用可能な場合のみ）
				DB::table('person_license')->where('staff_code', $staffCode)->delete();
				Log::info("🗑 古いライセンス情報は削除されました。");

				$lastId = DB::table('person_license')
					->where('staff_code', $staffCode)
					->max('id') ?? 0;

				foreach ($request->licenses as $license) {
					Log::info("📌 ライセンスを確認中です。", [
						'group_code' => $license['group_code'] ?? null,
						'category_code' => $license['category_code'] ?? null,
						'license_code' => $license['code'] ?? null
					]);

					if (empty($license['group_code']) || empty($license['category_code']) || empty($license['code'])) {
						Log::warning("⚠️ ライセンス情報が不完全です。");
						continue; // 次のループに進みますが、null値は入力されません⚠️ 
					}

					// ライセンスごとに個別の `$licenseName` を取得します。**
					$licenseName = DB::table('master_license')
						->where('group_code', $license['group_code'])
						->where('category_code', $license['category_code'])
						->where('code', $license['code'])
						->value('name');

					if (!$licenseName) {
						Log::warning("⚠️ 選択したライセンスが見つかりませんでした。", [
							'group_code' => $license['group_code'],
							'category_code' => $license['category_code'],
							'license_code' => $license['code']
						]);
						return back()->withErrors(['license_code' => '選択したライセンスは使用できません.']);
					}

					Log::info("✅ ライセンス利用可能: " . $licenseName);

					// ✅ Carbonでフォーマットされた日付
					// $getDay = !empty($license['get_day']) ? Carbon::createFromFormat('Ymd', $license['get_day'])->format('Y-m-d') : null;
					$getDay = null;
					if (!empty($license['get_day']) && preg_match('/^\d{8}$/', $license['get_day'])) {
						$getDay = Carbon::createFromFormat('Ymd', $license['get_day'])->format('Y-m-d');
					}

					// ライセンスをデータベースに入力する**
					DB::table('person_license')->insert([
						'staff_code' => $staffCode,
						'id' => ++$lastId, // この `staff_code` に属する ID のみ**
						'group_code' => $license['group_code'],
						'category_code' => $license['category_code'],
						'code' => $license['code'],
						'get_day' => $getDay,
						'remark' => $licenseName,
						'created_at' => now(),
						'update_at' => now(),
					]);
				}
				Log::info("✅ person_license テーブルが更新されました。");
			}


			DB::commit();
			Log::info("✅ すべてのデータが正常に保存されました。");
			$jobId = session('apply_job');
			if ($jobId) {
				session()->put('apply_job', $jobId);
				session()->save();  // ✅ セッションを強制保存
			}

			return redirect()->route('upload.form')->with('success', '自己PR・志望動機が保存されました。');
		} catch (\Exception $e) {
			DB::rollBack();
			Log::error("❌ 保存に失敗しました: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return redirect()->route('self_pr')
				->withErrors('保存に失敗しました: ' . $e->getMessage())
				->withInput(); // 👈 これらの入力を保存します
		}
	}
}
