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

		$offers = DB::table('person_offer')
			->select('order_code', 'company_code', 'offer_flag')
			->where('staff_code', $staffCode)
			->get();
		//->first();

		//dd($offers);
		// $hasOffer = $offers ? true : false;
		$hasOffer = !$offers->isEmpty();

		// デフォルト値
		$jobs = [];
		$companyName = null;

		if ($hasOffer) {
			foreach ($offers as $offer) {
				//dd($offer);
				//$flags[] = $offer->offar_flag
				$job = DB::table('job_order')
					->join('person_offer', 'job_order.order_code', '=', 'person_offer.order_code')
					->select('job_order.order_code', 'job_order.company_code', 'job_order.job_type_detail', 'person_offer.offer_flag')
					->where('job_order.order_code', $offer->order_code)
					->first();

				if ($job) {
					$jobs[] = $job;
				}
				//dd($jobs);


				$company = DB::table('master_company')
					->select('company_name_k')
					->where('company_code', $offer->company_code)
					->first();

				if ($company) {
					$companyName[] = $company;
				}
			}
		}
		//🔹 `offer_flag = 3`の場合、オファーは制限される必要があります
		$hasConfirmedCancel = collect($jobs)->contains('offer_flag', '2');
		// 🔹 Agar userning oldingi offeri tugatilgan bo‘lsa (`offer_flag = 4`), yangi offer bera olishi kerak
		$hasCompletedOffer = DB::table('person_offer')
			->where('staff_code', $staffCode)
			->where('offer_flag', '3') // Offer tugatilgan
			->exists();


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
			// ->when($desiredSalaryType === '時給' && !is_null($personHopeWorkingCondition) && $personHopeWorkingCondition->hourly_income_min > 0, function ($query) use ($personHopeWorkingCondition) {
			// 	return $query->where('job_order.hourly_income_min', '>=', $personHopeWorkingCondition->hourly_income_min);
			// })
			// ->when($desiredSalaryType === '年収' && !is_null($personHopeWorkingCondition) && $personHopeWorkingCondition->yearly_income_min > 0, function ($query) use ($personHopeWorkingCondition) {
			// 	return $query->where('job_order.yearly_income_min', '>=', $personHopeWorkingCondition->yearly_income_min);
			// })
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
			// }) // ✅ person_license ma’lumotlari ixtiyoriy holatda solishtirildi
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
		return view('mypage', compact('hasMatching', 'hasOffer', 'jobs', 'hasConfirmedCancel', 'companyName', 'matchingJobCount', 'hasCompletedOffer'));
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
				'mj.big_class_code',  // Bu qatorni tekshiring!
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

		// Agar `$careers` bo‘sh bo‘lsa, unga bo‘sh ob’ekt qo‘shamiz
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
			'careers.*.number_employees' => 'nullable|integer|min:1',
			'careers.*.entry_day_year' => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
			'careers.*.entry_day_month' => 'nullable|numeric|min:1|max:12',
			'careers.*.retire_day_year' => 'nullable|digits:4|integer|min:1900|max:' . (date('Y') + 10),
			'careers.*.retire_day_month' => 'nullable|numeric|min:1|max:12',
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
			dd($request->all(), $validator->errors());
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
				if (!empty($education['entry_day_year']) && !empty($education['entry_day_month'])) {
					$entryDay = "{$education['entry_day_year']}-" . sprintf('%02d', $education['entry_day_month']) . "-01";
					$graduateDay = "{$education['graduate_day_year']}-" . sprintf('%02d', $education['graduate_day_month']) . "-01";

					$existingId = DB::table('person_educate_history')
						->where('staff_code', $staffCode)
						->where('school_name', $education['school_name'])
						->value('id');

					if ($existingId) {
						// ✏️ 利用可能な場合は更新します。
						DB::table('person_educate_history')
							->where('id', $existingId)
							->where('staff_code', $staffCode) // ✅ Faqat ushbu foydalanuvchini tekshirish
							->update([
								'school_type_code' => $education['school_type_code'],
								'speciality' => $education['speciality'] ?? null,
								'course_type' => $education['course_type'] ?? null,
								'entry_day' => $entryDay,
								'graduate_day' => $graduateDay,
								'entry_type_code' => $education['entry_type_code'] ?? null,
								'graduate_type_code' => $education['graduate_type_code'] ?? null,
								'update_at' => now(),
							]);
					} else {
						// 🆕 新しいIDを取得して追加する
						$newId = DB::table('person_educate_history')
							->where('staff_code', $staffCode)
							->max('id');

						$newId = $newId ? $newId + 1 : 1;

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
					}
				}
			}

			// ✅ 2️⃣キャリア情報の維持
			$careers = $request->input('careers', []);
			foreach ($careers as $career) {
				if (!empty($career['entry_day_year']) && !empty($career['entry_day_month'])) {
					$entryDay = "{$career['entry_day_year']}-" . sprintf('%02d', (int)$career['entry_day_month']) . "-01";
					$retireDay = (!empty($career['retire_day_year']) && !empty($career['retire_day_month']))
						? "{$career['retire_day_year']}-" . sprintf('%02d', (int)$career['retire_day_month']) . "-01"
						: null;

					// ✅ 資本は新規エントリまたは更新時にのみ変換されます
					// $capital = $request->input('capital')
					// 	/? (int) $request->input('capital') * 1000
					// 	: null;
					// ✅ Capital maydonini to‘g‘ri hisoblash
					$existingRecord = DB::table('person_career_history')
						->where('staff_code', $staffCode)
						->where('company_name', $career['company_name'])
						->first(); // IDだけでなく、データ全体を取得します。
					$capital = isset($career['capital']) ? (int)$career['capital'] : 0;
					if ((!isset($career['id']) || $career['capital'] != $existingRecord->capital) && $capital > 0 && $capital < 1000) {
						$capital *= 1000;
					}

					$existingId = DB::table('person_career_history')
						->where('staff_code', $staffCode)
						->where('company_name', $career['company_name'])
						->value('id');

					if ($existingId) {
						// ✏️ 利用可能な場合は更新します。
						// ✏️ **ユーザー情報を更新**
						DB::table('person_career_history')
							->where('id', $existingId)
							->where('staff_code', $staffCode) // ⛔ Himoya: Boshqa foydalanuvchilarga ta’sir qilmaslik
							->update([
								'capital' => $capital,
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
					} else {
						// 🆕 新しいIDを取得して追加する
						$newId = DB::table('person_career_history')
							->where('staff_code', $staffCode)
							->max('id');

						$newId = $newId ? $newId + 1 : 1;

						DB::table('person_career_history')->insert([
							'id' => $newId,
							'staff_code' => $staffCode,
							'company_name' => $career['company_name'] ?? '',
							'capital' => $capital,
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
					}
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

		// dd($educationInsertData, $careerInsertData);

		// dd($request->all());
		// 保存成功の報告
		$jobId = session('apply_job');
		if ($jobId) {
			session()->put('apply_job', $jobId);
			session()->save();  // ✅ Sessiyani majburan saqlash
		}

		return redirect()->route('self_pr')->with('success', '学歴と職歴情報が保存されました。');
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

		// Foydalanuvchining shaxsiy ma'lumotlari
		$personDetails = DB::table('master_person')
			->where('staff_code', $staffCode)
			->select('marriage_flag', 'dependent_number', 'dependent_flag')
			->first() ?? new stdClass();

		// 🟢 Foydalanuvchining `self_pr` ma'lumotini olish
		$selfPR = DB::table('person_self_pr')
			->where('staff_code', $staffCode)
			->select('self_pr')
			->first(); // ?? new stdClass(); // Agar mavjud bo‘lmasa, bo‘sh obyekt

		// Barcha guruhlarni olish
		$groups = DB::table('master_license')
			->select('group_code', 'group_name')
			->distinct()
			->get();

		// Foydalanuvchining saqlangan litsenziyalari (id bo‘yicha tartiblash)
		$licenses = DB::table('person_license as pl')
			->join('master_license as ml', function ($join) {
				$join->on('pl.group_code', '=', 'ml.group_code')
					->on('pl.category_code', '=', 'ml.category_code');
			})
			->where('pl.staff_code', $staffCode)
			->orderBy('pl.id') // ✅ id bo‘yicha tartiblash
			->select('pl.id', 'pl.group_code', 'pl.category_code', 'pl.code', 'ml.category_name', 'ml.name')
			->get()
			->toArray(); // **Bo‘sh bo‘lsa, aniq array bo‘lishi uchun**

		$educations = DB::table('person_resume_other')
			->where('staff_code', $staffCode)
			->get()
			->toArray();
		// 🟢 **Kerakli kategoriyalarni faqat shu ro‘yxatdan olish**
		$categories = DB::table('master_code')
			->whereIn('category_code', ['OS', 'Application', 'DevelopmentLanguage', 'Database'])
			->select('category_code')
			->distinct()
			->pluck('category_code', 'category_code')
			->toArray();

		// 🟢 Har bir `category_code` bo‘yicha `master_code` jadvalidan skillar olish
		$skills = DB::table('master_code')
			->whereIn('category_code', array_keys($categories))
			->select('category_code', 'code', 'detail')
			->get()
			->groupBy('category_code');

		// 🟢 Foydalanuvchining oldin tanlagan skill kodlarini olish
		$selectedSkills = DB::table('person_skill')
			->where('staff_code', $staffCode)
			->pluck('code', 'category_code')
			->toArray();

		// Blade-ga yuborish
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
	// public function storePR(Request $request)
	// {
	// 	DB::enableQueryLog();

	// 	$person = Auth::user();
	// 	if (!$person) {
	// 		return redirect()->route('login')->withErrors('ログインしてください。');
	// 	}

	// 	$staffCode = $person->staff_code;
	// 	if (!$staffCode) {
	// 		return redirect()->route('self_pr')->withErrors('スタッフコードが見つかりません。');
	// 	}

	// 	// Formani tekshirish**
	// 	$request->validate([
	// 		'CONF_SelfPR' => 'nullable|string|max:2000',
	// 		'marriage_flag' => 'nullable|integer|in:0,1',
	// 		'dependent_number' => 'nullable|integer|min:0',
	// 		'dependent_flag' => 'nullable|integer|in:0,1',
	// 		'skills' => 'nullable|array',
	// 		'skills.*' => 'nullable|array',
	// 		'skills.*.*' => 'nullable|exists:master_code,code',
	// 		'educations' => 'nullable|array',
	// 	]);

	// 	try {
	// 		DB::beginTransaction();

	// 		// person_self_pr jadvaliga ma'lumot qo‘shish yoki yangilash**
	// 		DB::table('person_self_pr')->updateOrInsert(
	// 			['staff_code' => $staffCode],
	// 			[
	// 				'self_pr' => $request->input('CONF_SelfPR'),
	// 				'created_at' => now(),
	// 				'update_at' => now(),
	// 			]
	// 		);

	// 		// master_person yangilash**
	// 		DB::table('master_person')->updateOrInsert(
	// 			['staff_code' => $staffCode],
	// 			[
	// 				'marriage_flag' => $request->input('marriage_flag'),
	// 				'dependent_number' => $request->input('dependent_number'),
	// 				'dependent_flag' => $request->input('dependent_flag'),
	// 				'updated_at' => now(),
	// 			]
	// 		);

	// 		// person_resume_other yangilash**
	// 		if (!empty($request->educations)) {
	// 			foreach ($request->educations as $education) {
	// 				if (empty($education['subject'])) {
	// 					continue;
	// 				}

	// 				DB::table('person_resume_other')->updateOrInsert(
	// 					[
	// 						'staff_code' => $staffCode,
	// 						'subject' => $education['subject'],
	// 					],
	// 					[
	// 						'wish_motive' => $education['wish_motive'],
	// 						'hope_column' => $education['hope_column'],
	// 						'commute_time' => $education['commute_time'],
	// 						'created_at' => now(),
	// 						'update_at' => now(),
	// 					]
	// 				);
	// 			}
	// 		}

	// 		// Foydalanuvchining `person_skill` malumotlarini yangilash**
	// 		if (!empty($request->skills)) {
	// 			// Avval eski skillar o‘chiriladi faqat shu foydalanuvchi uchun**
	// 			DB::table('person_skill')->where('staff_code', $staffCode)->delete();

	// 			// ✅ Shu `staff_code` uchun eng katta `id` ni topish (0 dan boshlanadi)
	// 			$lastId = DB::table('person_skill')
	// 				->where('staff_code', $staffCode)
	// 				->max('id') ?? 0;

	// 			foreach ($request->skills as $categoryCode => $skills) {
	// 				foreach ($skills as $skillCode) {
	// 					DB::table('person_skill')->insert([
	// 						'staff_code' => $staffCode,
	// 						'id' => ++$lastId, // Faqat shu `staff_code`ga tegishli id**
	// 						'category_code' => $categoryCode,
	// 						'code' => $skillCode,
	// 						'period' => 0,
	// 						'start_day' => now(),
	// 						'created_at' => now(),
	// 						'update_at' => now(),
	// 					]);
	// 				}
	// 			}
	// 		}

	// 		if (!empty($request->licenses)) {
	// 			$licenseName = DB::table('master_license')
	// 				->where('group_code', $request->group_code)
	// 				->where('category_code', $request->category_code)
	// 				->where('code', $request->license_code)
	// 				->value('name');

	// 			if (!$licenseName) {
	// 				return back()->withErrors(['license_code' => '選択したライセンスは使用できません.']);
	// 			}
	// 			// ✅ Avval ushbu foydalanuvchiga tegishli eski litsenziyalarni o‘chiramiz
	// 			DB::table('person_license')->where('staff_code', $staffCode)->delete();

	// 			// ✅ Shu `staff_code` uchun eng katta `id` ni topish (0 dan boshlanadi)
	// 			$lastId = DB::table('person_license')
	// 				->where('staff_code', $staffCode)
	// 				->max('id') ?? 0;

	// 			foreach ($request->licenses as $license) {
	// 				if (empty($license['group_code']) || empty($license['category_code']) || empty($license['code'])) {
	// 					continue;
	// 				}

	// 				DB::table('person_license')->insert([
	// 					'staff_code' => $staffCode,
	// 					'id' => ++$lastId, // Faqat shu `staff_code`ga tegishli id**
	// 					'group_code' => $license['group_code'],
	// 					'category_code' => $license['category_code'],
	// 					'code' => $license['code'],
	// 					'get_day' => now()->toDateString(),
	// 					'remark' => $licenseName,
	// 					'created_at' => now(),
	// 					'update_at' => now(),
	// 				]);
	// 			}
	// 		}
	// 		//dd($request->licenses);


	// 		DB::commit();
	// 		return redirect()->route('upload.form')->with('success', '自己PR・志望動機が保存されました。');
	// 	} catch (\Exception $e) {
	// 		DB::rollBack();
	// 		Log::error("❌ 保存に失敗しました: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
	// 		return redirect()->route('self_pr')->withErrors('保存に失敗しました: ' . $e->getMessage());
	// 	}
	// }

	public function storePR(Request $request)
	{
		DB::enableQueryLog();
		Log::info("🔹 storePR() metodi chaqirildi.");

		$person = Auth::user();
		if (!$person) {
			Log::warning("⚠️ Foydalanuvchi aniqlanmadi. Login sahifasiga yo‘naltirilmoqda.");
			return redirect()->route('login')->withErrors('ログインしてください。');
		}

		$staffCode = $person->staff_code;
		if (!$staffCode) {
			Log::warning("⚠️ Foydalanuvchi `staff_code` topilmadi.");
			return redirect()->route('self_pr')->withErrors('スタッフコードが見つかりません。');
		}

		Log::info("✅ Foydalanuvchi aniqlandi", ['staff_code' => $staffCode]);

		// Formani tekshirish
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
			Log::info("🛠 Transaction boshlanmoqda...");

			// person_self_pr jadvaliga ma'lumot qo‘shish yoki yangilash
			DB::table('person_self_pr')->updateOrInsert(
				['staff_code' => $staffCode],
				[
					'self_pr' => $request->input('CONF_SelfPR'),
					'created_at' => now(),
					'update_at' => now(),
				]
			);
			Log::info("✅ person_self_pr jadvaliga malumot qo‘shildi yoki yangilandi.");

			// master_person yangilash
			DB::table('master_person')->updateOrInsert(
				['staff_code' => $staffCode],
				[
					'marriage_flag' => $request->input('marriage_flag'),
					'dependent_number' => $request->input('dependent_number'),
					'dependent_flag' => $request->input('dependent_flag'),
					'updated_at' => now(),
				]
			);
			Log::info("✅ master_person jadvali yangilandi.");

			// person_resume_other yangilash
			if (!empty($request->educations)) {
				Log::info("🎓 Ta'lim ma'lumotlari saqlanmoqda...");
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
				Log::info("✅ person_resume_other jadvali yangilandi.");
			}

			// Foydalanuvchining `person_skill` ma'lumotlarini yangilash
			if (!empty($request->skills)) {
				Log::info("💡 Skillar yangilanmoqda...");

				// Eski ma'lumotlarni o‘chirish
				DB::table('person_skill')->where('staff_code', $staffCode)->delete();
				Log::info("🗑 Eski skill ma'lumotlari o‘chirildi.");

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
				Log::info("✅ person_skill jadvali yangilandi.");
			}

			// Litsenziya ma'lumotlarini saqlash
			if (!empty($request->licenses)) {
				Log::info("📜 Litsenziyalar saqlanmoqda...");

				// Eski ma'lumotlarni o‘chirish (faqat yangi ma'lumotlar mavjud bo'lsa)
				DB::table('person_license')->where('staff_code', $staffCode)->delete();
				Log::info("🗑 Eski litsenziya ma'lumotlari o‘chirildi.");

				$lastId = DB::table('person_license')
					->where('staff_code', $staffCode)
					->max('id') ?? 0;

				foreach ($request->licenses as $license) {
					Log::info("📌 Litsenziya tekshirilmoqda.", [
						'group_code' => $license['group_code'] ?? null,
						'category_code' => $license['category_code'] ?? null,
						'license_code' => $license['code'] ?? null
					]);

					if (empty($license['group_code']) || empty($license['category_code']) || empty($license['code'])) {
						Log::warning("⚠️ Litsenziya ma'lumotlari to'liq emas.");
						continue; // ⚠️ Keyingi siklga o'tamiz, null qiymatlar kiritilmaydi
					}

					// **Har bir litsenziya uchun alohida `$licenseName` olish!**
					$licenseName = DB::table('master_license')
						->where('group_code', $license['group_code'])
						->where('category_code', $license['category_code'])
						->where('code', $license['code'])
						->value('name');

					if (!$licenseName) {
						Log::warning("⚠️ Tanlangan litsenziya topilmadi.", [
							'group_code' => $license['group_code'],
							'category_code' => $license['category_code'],
							'license_code' => $license['code']
						]);
						return back()->withErrors(['license_code' => '選択したライセンスは使用できません.']);
					}

					Log::info("✅ Litsenziya mavjud: " . $licenseName);

					// **Litsenziyani bazaga kiritish**
					DB::table('person_license')->insert([
						'staff_code' => $staffCode,
						'id' => ++$lastId, // Faqat shu `staff_code`ga tegishli id**
						'group_code' => $license['group_code'],
						'category_code' => $license['category_code'],
						'code' => $license['code'],
						'get_day' => now()->toDateString(),
						'remark' => $licenseName,
						'created_at' => now(),
						'update_at' => now(),
					]);
				}
				Log::info("✅ person_license jadvali yangilandi.");
			}


			DB::commit();
			Log::info("✅ Barcha malumotlar muvaffaqiyatli saqlandi.");
			$jobId = session('apply_job');
			if ($jobId) {
				session()->put('apply_job', $jobId);
				session()->save();  // ✅ Sessiyani majburan saqlash
			}

			return redirect()->route('upload.form')->with('success', '自己PR・志望動機が保存されました。');
		} catch (\Exception $e) {
			DB::rollBack();
			Log::error("❌ 保存に失敗しました: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return redirect()->route('self_pr')->withErrors('保存に失敗しました: ' . $e->getMessage());
		}
	}
}
