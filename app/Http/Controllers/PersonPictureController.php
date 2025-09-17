<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonPicture;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
// use Barryvdh\DomPDF\Facade\Pdf;
use TCPDF;
use FPDF;
use Mpdf\Mpdf;
use App\Http\Controllers\ExportController;
use stdClass;


class PersonPictureController extends Controller
{

	public function showUploadForm()
	{
		Log::info("📌 showUploadForm: ユーザーがアップロード ページにアクセスしました。.");

		$person = Auth::user();
		$staffCode = $person ? $person->staff_code : null;
		$userImage = null;
		$successMessage = session('success');
		$errorMessage = session('error');

		if ($staffCode) {
			Log::info("✅ showUploadForm: staff_code = {$staffCode}");

			$user = DB::table('person_picture')->where('staff_code', $staffCode)->first();
			if ($user && !empty($user->picture)) {
				Log::info("🖼 showUploadForm: ユーザーに写真があります。.");
				// $userImage = 'data:image/jpeg;base64,' . base64_encode($user->picture);
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mimeType = finfo_buffer($finfo, $user->picture);
				finfo_close($finfo);

				$userImage = 'data:' . $mimeType . ';base64,' . base64_encode($user->picture);
			} elseif (!$user) {
				Log::info("ℹ️ showUploadForm: ユーザーにはまだ写真レコードがありません。 (Foydalanuvchida hali rasm mavjud emas)");
			} else {
				Log::warning("⚠️ showUploadForm: ユーザーの写真は空です。.");
			}
		}

		return view('upload', compact('staffCode', 'userImage', 'successMessage', 'errorMessage'));
	}

	public function uploadTemporary(Request $request)
	{
		Log::info("📤 uploadTemporary: 画像は一時的に読み込まれています。...", ['staff_code' => $request->staff_code]);

		// $request->validate([
		// 	'staff_code' => 'required|string|max:8',
		// 	'picture' => 'required|image|mimes:jpeg,png|max:500', // JPEG or pngのみ、500KB未満
		// ]);

		$request->validate([
			'staff_code' => 'required|string|max:8',
			'picture' => 'required|image|mimes:jpeg,jpg,png|max:500',
		]);

		Log::info("✅ uploadTemporary: ユーザーが検証されました。.");

		try {
			$imageData = file_get_contents($request->file('picture'));
			Log::info("🖼 uploadTemporary: ファイルの読み取り、サイズ: " . strlen($imageData) . "バイト");

			//return response()->json(['image' => 'data:image/jpeg;base64,' . base64_encode($imageData)]);
			//20250323 Change
			$mimeType = $request->file('picture')->getMimeType(); // 例: image/png
			return response()->json(['image' => 'data:' . $mimeType . ';base64,' . base64_encode($imageData)]);
		} catch (\Exception $e) {
			Log::error("❌ uploadTemporary: エラー! " . $e->getMessage());
			return response()->json(['error' => '画像の読み取り中にエラーが発生しました。!'], 500);
		}
	}
	public function confirmUpload(Request $request)
	{
		Log::info("📥 confirmUpload chaqirildi.", ['staff_code' => $request->staff_code]);

		// $request->validate([
		// 	'staff_code' => 'required|string|max:8',
		// 	'image' => 'required|image|mimes:jpeg,jpg,png|max:500', // JPEG or PNG は許可されますが、500KB の制限があります
		// ]);

		$request->validate([
			'staff_code' => 'required|string|max:8',
			'picture' => 'required|image|mimes:jpeg,jpg,png|max:500',
		]);

		$staffCode = $request->staff_code;

		try {
			// $imageData = file_get_contents($request->file('image')->getRealPath());
			$imageData = file_get_contents($request->file('picture')->getRealPath());

			DB::table('person_picture')->updateOrInsert(
				['staff_code' => $staffCode],
				[
					'picture' => $imageData,
					'created_at' => now(),
					'update_at' => now(),
				]
			);

			Log::info("✅ confirmUpload: 画像をデータベースに保存しました!", ['staff_code' => $staffCode]);
			session()->put('apply_job', session('apply_job', null));
			return response()->json([
				'success' => true,
				'message' => '写真を保存しました!',
				'redirect' => route('resume.preview')
			]);
		} catch (\Exception $e) {
			Log::error("❌ confirmUpload: データベースへの書き込みエラー! " . $e->getMessage());
			return response()->json(['error' => 'エラー: ' . $e->getMessage()], 500);
		}
	}

	public function previewResume()
	{
		$person = Auth::user();
		if (!$person) {
			return redirect()->route('login')->withErrors('ログインしてください。');
		}

		$staffCode = $person->staff_code;
		$userImage = null;
		// 🛠 都道府県名の取得
		$personPrefecture = DB::table('master_person')
			->join('master_code', 'master_person.prefecture_code', '=', 'master_code.code')
			->where('master_code.category_code', 'Prefecture')
			->where('master_person.staff_code', $staffCode) // そうです、それはオブジェクトではなく文字列だからです。
			->pluck('master_code.detail');
		$person = DB::table('master_person')
			->where('staff_code', $staffCode)
			->select(
				'name',
				'name_f as katakana_name',
				'birthday',
				DB::raw('YEAR(birthday) as birth_year'),
				DB::raw('MONTH(birthday) as birth_month'),
				DB::raw('DAY(birthday) as birth_day'),
				'address',
				'city',
				'town',
				'age',
				'sex',
				'portable_telephone_number',
				'mail_address',
				'post_u',
				'post_l',
				'marriage_flag',
				'dependent_number',
				'dependent_flag'
			)
			->first();
		$person->gender = match ((int) $person->sex) {
			1 => '男',
			2 => '女',
			default => '' // 含まれていない場合
		};
		$age = $person && $person->birthday ? Carbon::parse($person->birthday)->age : null;
		// dd($person->gender);

		// 写真を撮る
		$user = DB::table('person_picture')->where('staff_code', $staffCode)->first();
		if ($user && !empty($user->picture)) {
			// $userImage = 'data:image/jpeg;base64,' . base64_encode($user->picture);
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimeType = finfo_buffer($finfo, $user->picture);
			finfo_close($finfo);

			$userImage = 'data:' . $mimeType . ';base64,' . base64_encode($user->picture);
		}

		// ユーザーの学歴と職歴
		$educations = DB::table('person_educate_history as edu')
			->leftJoin('master_code as mc_entry', function ($join) {
				$join->on('mc_entry.code', '=', 'edu.entry_type_code')
					->where('mc_entry.category_code', '=', 'EntryType');
			})
			->leftJoin('master_code as mc_grad', function ($join) {
				$join->on('mc_grad.code', '=', 'edu.graduate_type_code')
					->where('mc_grad.category_code', '=', 'GraduateType');
			})
			->select(
				'edu.school_name',
				DB::raw('YEAR(edu.entry_day) as entry_day_year'),
				DB::raw('LPAD(MONTH(edu.entry_day), 2, "0") as entry_day_month'),
				DB::raw('YEAR(edu.graduate_day) as graduate_day_year'),
				DB::raw('LPAD(MONTH(edu.graduate_day), 2, "0") as graduate_day_month'),
				'mc_entry.detail as entry_type',
				'mc_grad.detail as graduate_type'
			)
			->where('edu.staff_code', $staffCode)
			->get();


		$careers = DB::table('person_career_history as job')
			->leftJoin('master_code as mc_industry', function ($join) {
				$join->on('mc_industry.code', '=', 'job.industry_type_code')
					->where('mc_industry.category_code', '=', 'IndustryTypeDsp');
			})
			->leftJoin('master_code as mc_working', function ($join) {
				$join->on('mc_working.code', '=', 'job.working_type_code')
					->where('mc_working.category_code', '=', 'WorkingType');
			})
			->select(
				'job.company_name',
				DB::raw('YEAR(job.entry_day) as entry_day_year'),
				DB::raw('LPAD(MONTH(job.entry_day), 2, "0") as entry_day_month'),
				DB::raw('YEAR(job.retire_day) as retire_day_year'),
				DB::raw('LPAD(MONTH(job.retire_day), 2, "0") as retire_day_month'),
				'job.job_type_detail',
				'mc_industry.detail as industry_type',
				'mc_working.detail as working_type',
				'job.number_employees',
				'job.capital',
				'job.business_detail'
			)
			->where('job.staff_code', $staffCode)
			->get();

		$age = $person && $person->birthday ? Carbon::parse($person->birthday)->age : null;
		// すべてのグループを取得
		$groups = DB::table('master_license')
			->select('group_code', 'group_name')
			->distinct()
			->get();

		// ユーザーが保存したライセンス (ID で並べ替え)
		$licenses = DB::table('person_license as pl')
			->join('master_license as ml', function ($join) {
				$join->on('pl.group_code', '=', 'ml.group_code')
					->on('pl.category_code', '=', 'ml.category_code')
					->on('pl.code', '=', 'ml.code'); // ✅ `code`も追加されました
			})
			->where('pl.staff_code', $staffCode)
			->orderBy('pl.id') // ✅ IDで並べ替え
			->select(
				'pl.id',
				'pl.group_code',
				'pl.category_code',
				'pl.code',
				'ml.group_name',
				'ml.category_name',
				'ml.name',
				'pl.get_day'
			)
			->get()
			->toArray(); // 空の場合は明示的な配列となる


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

		// 🟢 ユーザーが以前に選択したスキルコードを取得し、master_codeに関連付けます。
		$selectedSkills = DB::table('person_skill as ps')
			->join('master_code as mc', function ($join) {
				$join->on('ps.category_code', '=', 'mc.category_code')
					->on('ps.code', '=', 'mc.code'); // ✅ `code` がリンクされています
			})
			->where('ps.staff_code', $staffCode)
			->select('ps.category_code', 'ps.code', 'mc.detail as skill_name')
			->get()
			->groupBy('category_code'); // ✅ カテゴリ別にグループ化

		// 🟢 ユーザーの`self_pr`情報を取得する
		$selfPR = DB::table('person_self_pr')
			->where('staff_code', $staffCode)
			->select('self_pr')
			->first() ?? new stdClass(); // 利用できない場合は空のオブジェクト
		$resumeOther = DB::table('person_resume_other')
			->where('staff_code', $staffCode)
			->select('subject', 'wish_motive', 'hope_column', 'commute_time')
			->first() ?? new stdClass();

		// ✅ 新しいcommotve_timeフォーマット
		$commuteMinutes = (int) ($resumeOther->commute_time ?? 0);
		$commuteHours = intdiv($commuteMinutes, 60);
		$commuteMinutes = $commuteMinutes % 60;

		// 🔍 Tekshirish
		//dd($commuteHours, $commuteMinutes);

		session()->put('apply_job', session('apply_job', null));
		return view('resume_preview', compact(
			'staffCode',
			'person',
			'userImage',
			'educations',
			'careers',
			'groups',
			'licenses',
			'personPrefecture',
			'selfPR',
			'categories',
			'selectedSkills',
			'commuteHours',
			'commuteMinutes',
			'resumeOther',
			'age'
		));
	}

	public function downloadResume($staffCode)
	{
		$exportController = new ExportController();
		$files = $exportController->generateResumeFiles($staffCode);

		if (!$files) {
			return redirect()->back()->withErrors('履歴書の作成中にエラーが発生しました！');
		}

		$file_path = storage_path('app/exports/pdf/' . $files . '.pdf');

		if (!file_exists($file_path)) {
			return redirect()->back()->withErrors('ファイルが見つかりません！');
		}

		return response()->download($file_path)->deleteFileAfterSend(true);
	}
}
