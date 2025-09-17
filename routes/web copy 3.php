<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MasterCompanyController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\MatchingsController;
use App\Http\Controllers\MatchingUpdateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyDashboardController;
use App\Http\Controllers\MailConfigController;
use App\Http\Controllers\AgentDashboardController;
use App\Http\Controllers\AgentJobController;
use App\Http\Controllers\CreateJobController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Auth\CompanyLoginController;
use App\Http\Controllers\Auth\AgentLoginController;
use App\Http\Controllers\ExportContoroller;
use App\Http\Controllers\PersonPictureController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//use app\Exports\CustomeExport
use App\Http\Controllers\CsvUploadController;
use App\Http\Controllers\PublicMatchingController;
use App\Http\Controllers\BasicInfoController;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\LogExportController;
use App\Http\Livewire\PublicJobSearch;
use App\Http\Controllers\ResumeController;


Route::get('/unauthorized', function () {
    return response()->view('errors.unauthorized', [], 403);
})->name('unauthorized');
// メインページ
Route::get('/', function () {
    return view('welcome');
});
// Route::get('/', [PublicMatchingController::class, 'index'])->name('top');

Route::get('/master', function () {
    return view('master');
})->name('master');

Route::get('/export-job-log', [LogExportController::class, 'export'])->name('export.joblog');

// Route::get('/log-test', function () {
//     Log::channel('job_search_log')->info('✅ Custom job_search_log yaratildi!');
//     return 'Log yozildi!';
// });
Route::get('/landing', [RegisterController::class, 'landing'])->name('landing');
// メール確認ページへ
// 登録するには
// Route::get('/register', [RegisterController::class, 'register'])->name('register.get');
Route::get('/signin', [RegisterController::class, 'showEmailCreate'])->name('signin');
Route::post('/signin/register', [RegisterController::class, 'registration'])->name('registration');
Route::get('/register', [BasicInfoController::class, 'showRegisterForm'])->name('register.form');
Route::post('/register', [BasicInfoController::class, 'store'])->name('register');
// Route::post('/get-address-zipcloud', [RegisterController::class, 'getAddressFromZipcloud'])->name('get.address.zipcloud');
// Route::get('/resume/preview', [ResumeController::class, 'preview'])->name('resume.preview');
// Route::post('/resume/confirm', [ResumeController::class, 'confirm'])->name('resume.confirm');
// Route::middleware('auth')->group(function () {
//     Route::get('/matchings/match', [RegisterController::class, 'showMatchCreate'])->name('matchings.match');
// });
// Route::get('/matchings/match', [RegisterController::class, 'showMatchCreate'])->name('matchings.match');
// Route::match(['GET', 'POST'], '/matchings/matchstore', [RegisterController::class, 'createMatchStore'])->name('matchings.matchstore');
// Route::match(['GET', 'POST'], '/matchings/showmatch', [RegisterController::class, 'showMatch'])->name('matchings.showmatch');
// Route::match(['GET', 'POST'], '/matchings/matchstore', [RegisterController::class, 'createMatchStore'])->name('matchings.matchstore');
// Route::match(['GET', 'POST'], '/matchings/filterJobs', [RegisterController::class, 'filterJobs'])->name('matchings.filterJobs');
// Route::match(['GET', 'POST'], '/matchings/showmatch', [RegisterController::class, 'showMatch'])->name('matchings.showmatch');
Route::middleware('auth')->group(function () {
    Route::match(['GET', 'POST'], '/matchings/match-form', [RegisterController::class, 'showMatchCreate'])->name('matchings.match.form');
    Route::get('/matchings/match', [PublicMatchingController::class, 'index'])->name('matchings.match');
    // Route::match(['GET', 'POST'], '/matchings/match', [RegisterController::class, 'showMatchCreate'])->name('matchings.match');
    // Route::get('/matchings/match', [PublicMatchingController::class, 'index'])->name('matchings.match');
    // Route::get('/', [PublicMatchingController::class, 'index'])->name('top');
    Route::match(['GET', 'POST'], '/matchings/matchstore', [RegisterController::class, 'createMatchStore'])->name('matchings.matchstore');
    Route::match(['GET', 'POST'], '/matchings/showmatch', [RegisterController::class, 'showMatch'])->name('matchings.showmatch');
    Route::match(['GET', 'POST'], '/matchings/filterJobs', [RegisterController::class, 'filterJobs'])->name('matchings.filterJobs');
});

// Route::post('/matchings/filterJobs', [RegisterController::class, 'filterJobs'])->name('matchings.filterJobs');
Route::get('/api/job-categories/{big_class_code}', [PublicMatchingController::class, 'getJobCategories']);
Route::post('/initial-search', [PublicMatchingController::class, 'storeInitialSearch']);

// web.php or api.php
// Route::get('/api/big-classes', [PublicMatchingController::class, 'getBigClasses']);
// Route::get('/api/job-categories/{big_class_code}', [PublicMatchingController::class, 'getJobCategories']);


Route::get('/resume', [MypageController::class, 'resume'])->name('resume');
// Route::post('/filter-jobs', [RegisterController::class, 'filterJobs'])->name('matchings.filterJobs');
Route::get('/jobs/search', [PublicMatchingController::class, 'index'])->name('jobs.search');
Route::get('/jobs/detail/{id}', [PublicMatchingController::class, 'detail'])->name('jobs.detail');
// ログイン用
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/account/delete', [LoginController::class, 'deleteAccount'])->name('account.delete');

Route::get('/resume/basic-info', function () {
    return view('resume-page');
})->middleware('auth')->name('resume.basic-info'); // ← ログインユーザーである必要があります

// サインイン

Route::get('/verify-email/{token}', [RegisterController::class, 'verifyEmail'])->name('verify.email');
// フルネームとパスワードの入力ページを表示する
Route::get('/set-password', [RegisterController::class, 'showSetPasswordForm'])->name('set-password');
Route::post('/set-password', [RegisterController::class, 'setPassword'])->name('set-password.post');

// Route::get('/company_create', [CompanyRegisterController::class, 'showRegisterForm'])->name('company_create');
// Route::post('/create_company', [CompanyRegisterController::class, 'store'])->name('create_company.store');
// Route::get('/company_page', [CompanyRegisterController::class, 'CompanyPage'])->name('company_page');

// config/mail.php を表示するには
Route::get('/mail-config', [MailConfigController::class, 'index'])->name('mail.config');
Route::get('/refresh-mail-config', [MailConfigController::class, 'refreshConfig'])->name('mail.config.refresh');

// Example route
Route::get('/example', function () {
    return 'Success!';
})->middleware('check.something');

//セッションデータを確認する
Route::get('/session-info', function () {
    return response()->json([
        'cookie_name' => config('session.cookie'),
        'user_id' => session('user_id'),
        'role' => session('role'),
        'agent_data' => session('agent_data'),
    ]);
});


// 認証後のユーザーページ (ミドルウェアによって保護されたルート)
Route::middleware('auth')->group(function () {
    // マイページ
    Route::get('/mypage', [MypageController::class, 'myPage'])->name('mypage');
    Route::get('/educate-history', [MypageController::class, 'showEducateStoryForm'])->name('educate-history');
    Route::post('/educate-history/store', [MypageController::class, 'storeEducateHistory'])->name('educate-history.store');
    Route::get('/self_pr', [MypageController::class, 'showPRStoryForm'])->name('self_pr');
    Route::post('/self_pr/store', [MypageController::class, 'storePR'])->name('self_pr.store');
    Route::post('/educate-history/{id}/delete', [MypageController::class, 'destroyEducation'])
        ->middleware('auth');
    Route::delete('/career-history/{id}', [MypageController::class, 'destroyCareer'])->middleware('auth');
    //証明写真のアップロード
    // Route::get('/upload', [PersonPictureController::class, 'showUploadForm'])->name('upload.form');
    // Route::post('/upload', [PersonPictureController::class, 'upload'])->name('upload');
    Route::get('/upload', [PersonPictureController::class, 'showUploadForm'])->name('upload.form');
    Route::post('/upload-temp', [PersonPictureController::class, 'uploadTemporary'])->name('upload.temporary');
    Route::post('/confirm-upload', [PersonPictureController::class, 'confirmUpload'])->name('upload.confirm');
    Route::get('/download-resume/{staff_code}', [PersonPictureController::class, 'downloadResume'])->name('download.resume');

    Route::get('/resume/preview', [PersonPictureController::class, 'previewResume'])->name('resume.preview');

    Route::get('/resume/download/{staffCode}', [PersonPictureController::class, 'downloadResume'])->name('resume.download');

    Route::post('/resume/skip', function () {
        // session()->forget('apply_job');

        return response()->json([
            'redirect' => route('resume.preview')
        ]);
    })->name('resume.skip');

    Route::post('/resume/continue', function () {
        $jobId = session('apply_job'); // 仕事のセッションを始めましょう。
        if ($jobId) {
            return response()->json(['redirect' => route('resume.preview')]); // 🟢 `resume.preview` に切り替える
        }
        return response()->json(['redirect' => route('resume.preview')]);
    })->name('resume.continue');

    Route::get('/matchings/detail/{id}', function ($id) {
        session(['apply_job' => $id]);  // ✅ セッションを適切に保存する
        return redirect()->route('matchings.detail', ['id' => $id]); // ✅ 職場復帰ページ
    })->name('matchings.detail');

    Route::post('/resume/proceed', function () {
        //dd(session('apply_job')); // 🚀 **セッションの空き状況を確認するには追加してください**
        if (session()->has('apply_job')) {
            $jobId = session('apply_job');
            return redirect()->route('matchings.detail', ['id' => $jobId]);
        }
        return redirect()->route('mypage');
    })->name('resume.proceed');



    Route::get('/session/check', function () {
        return response()->json(['apply_job' => session('apply_job')]);
    })->name('session.check');




    // マッチングのページ
    // Route::get('/matchings/create', [MatchingsController::class, 'create'])->name('matchings.create');

    Route::get('/matchings/results', [MatchingsController::class, 'showMatchingResults'])->name('matchings.results');
    Route::get('/matchings/detail/{id}', [MatchingsController::class, 'detail'])->name('matchings.detail');
    Route::get('/get-job-types', [MatchingsController::class, 'getJobTypes']);
    Route::get('/get-license-categories', [MatchingsController::class, 'getLicenseCategories']);
    Route::get('/get-licenses', [MatchingsController::class, 'getLicenses']);
    Route::post('/matchings/store', [MatchingsController::class, 'store'])->name('matchings.store');
    Route::get('/matchings/search', [MatchingsController::class, 'search'])->name('matchings.search');
    // 検索結果を更新するフォーム
    Route::get('/matchings/update', [MatchingUpdateController::class, 'update'])->name('matchings.updateForm');
    // 更新結果の表示
    Route::get('/matchings/update-results', [MatchingUpdateController::class, 'updateResults'])->name('matchings.updateResults');
    //プロフィール
    Route::get('/profile/profile', [ProfileController::class, 'profile'])->name('profile.profile');
});
Route::middleware('auth')->group(function () {
    Route::delete('/license/{id}', [MypageController::class, 'destroyLicense'])->name('license.destroy');
});
Route::middleware('auth')->group(function () {
    Route::get('/matchings/create', [MatchingsController::class, 'edit'])->name('matchings.create');
    Route::put('/matchings/update', [MatchingsController::class, 'update'])->name('matchings.update');
});
// 希望職種（希望職種）に関連付けられた職種（middle_class_name）を取得します。
Route::get('/get-job-types', function (Request $request) {
    $bigClassCode = $request->query('big_class_code');
    $middleClasses = DB::table('master_job_type')
        ->where('big_class_code', $bigClassCode)
        ->select('middle_class_code', 'middle_clas_name')
        ->whereNotIn('middle_class_code',  ['00'])
        ->get();
    return response()->json($middleClasses);
});
Route::get('/api/job-types/{bigClassCode}', function ($bigClassCode) {
    $jobTypes = DB::table('master_job_type')
        ->where('big_class_code', $bigClassCode)
        ->select('middle_class_code as code', 'middle_clas_name as detail')
        ->whereNotIn('middle_class_code',  ['00'])
        ->get();

    return response()->json($jobTypes);
});

// license for create
// Route::get('/get-license-categories', function (Request $request) {
//     // 📌 パラメータを取得
//     $groupCode = $request->query('group_code');

//     // 📌 パラメータが空の場合はエラーを返します。
//     if (!$groupCode) {
//         return response()->json(['error' => 'Group code is required'], 400);
//     }

//     //データの取得 📌 データの取得
//     $categories = DB::table('master_license')
//         ->where('group_code', $groupCode)
//         ->select('category_code', 'category_name')
//         ->distinct()
//         ->get();

//     //情報が見つからない場合は報告してください 📌 情報が見つからない場合は報告してください。
//     if ($categories->isEmpty()) {
//         return response()->json(['message' => 'No categories found'], 404);
//     }

//     //JSON形式で返す 📌 
//     return response()->json(['categories' => $categories], 200);
// });
// Route::get('/get-licenses', function (Request $request) {
//     $groupCode = $request->input('group_code');
//     $categoryCode = $request->input('category_code');

//     return response()->json([
//         'licenses' => DB::table('master_license')
//             ->where('group_code', $groupCode)
//             ->where('category_code', $categoryCode)
//             ->select('code', 'name')
//             ->distinct()
//             ->get()
//             ->unique('code')
//     ]);
// });
Route::get('/get-license-categories', function (Request $request) {
    $groupCode = $request->query('group_code');

    if (!$groupCode) {
        return response()->json(['error' => 'Group code is required'], 400);
    }

    $categories = DB::table('master_license')
        ->where('group_code', $groupCode)
        ->select('category_code', 'category_name')
        ->distinct()
        ->get();

    if ($categories->isEmpty()) {
        return response()->json(['message' => 'No categories found'], 404);
    }

    return response()->json(['categories' => $categories], 200);
});

Route::get('/get-licenses', function (Request $request) {
    $groupCode = $request->query('group_code');
    $categoryCode = $request->query('category_code');

    return response()->json([
        'licenses' => DB::table('master_license')
            ->where('group_code', $groupCode)
            ->where('category_code', $categoryCode)
            ->select('code', 'name')
            ->distinct()
            ->get()
            ->unique('code')
    ]);
});
// リセットパスワード
// パスワードリセットフォームを表示
Route::get('/reset_password', [LoginController::class, 'resetPasswordRequestForm'])->name('reset_password.form');

// パスワードリセットリクエストを送信する
Route::post('/reset_password', [LoginController::class, 'resetPasswordRequest'])->name('reset_password.request');

// パスワードリセットフォーム
Route::get('/password/reset', [LoginController::class, 'showResetPasswordForm'])->name('password.reset.form');

// パスワードの更新
Route::post('/password/reset', [LoginController::class, 'resetPassword'])->name('password.update');


//求人企業
// ログインページを表示する
Route::get('company/login', [CompanyLoginController::class, 'showLoginForm'])->name('company.login');

// ログインデータの処理
Route::post('company/login', [CompanyLoginController::class, 'companyLogin'])->name('company.login.submit');

// ログインデータの処理
// Route::post('company/login', [CompanyLoginController::class, 'companyLogin'])->name('company.login.submit');

// Route::post('company/login', [CompanyLoginController::class, 'companyLogin'])->name('company.login');
Route::get('company/dashboard', [CompanyDashboardController::class, 'dashboard'])
    ->name('company.dashboard')
    ->middleware('auth:master_company');

Route::get('jobs/job_list', [CompanyDashboardController::class, 'showJobsList'])
    ->name('jobs.job_list')
    ->middleware('auth:master_company');
Route::get('jobs/create_job', [CreateJobController::class, 'showCreateJobPage'])->name('jobs.create_job');
Route::post('jobs/create_job', [CreateJobController::class, 'storeJob'])->name('jobs.create_job.post');

Route::middleware('auth:master_company')->group(function () {
    Route::get('/jobs/{id}', [CompanyDashboardController::class, 'detail'])->name('jobs.job_detail');
});
Route::post('/company/logout', [CompanyLoginController::class, 'logout'])->name('company.logout');
// 求人を更新する！
Route::get('/jobs/{orderCode}/edit', [CreateJobController::class, 'showEditJobPage'])->name('jobs.edit');
Route::put('/jobs/{orderCode}', [CreateJobController::class, 'updateJob'])->name('jobs.update');

//エージェント
Route::get('agent/login', [AgentLoginController::class, 'showLoginForm'])->name('agent.login');
Route::post('agent/login', [AgentLoginController::class, 'agentLogin'])->name('agent.login.submit');

Route::get('agent/dashboard', function () {
    $companyUser = Auth::guard('master_company')->user();
    $agentUser = Auth::guard('master_agent')->user();
    return view('agent.agent_dashboard', compact('companyUser', 'agentUser'));
})->name('agent.dashboard')->middleware('auth:master_agent');

Route::get('agent/details', [AgentDashboardController::class, 'showAgentDetails'])->name('agent.details')->middleware('auth:master_agent');
Route::get('agent/profile', [AgentDashboardController::class, 'showAgentProfile'])->name('agent.profile')->middleware('auth:master_agent');
Route::get('agent/linked-companies', [AgentDashboardController::class, 'showLinkedCompanies'])->name('agent.linked_companies')->middleware('auth:master_agent');
Route::get('agent/linked-jobs', [AgentDashboardController::class, 'showLinkedJobs'])->name('agent.linked_jobs')->middleware('auth:master_agent');
// 会社の求人詳細ページへのルート
Route::get('/agent/company-job-details/{order_code}', [AgentDashboardController::class, 'showCompanyJobDetails'])
    ->name('agent.company_job_details')
    ->middleware('auth:master_agent');
Route::post('/agent/logout', [AgentLoginController::class, 'logout'])->name('agent.logout');
// Route::post('/agent/logout', [App\Http\Controllers\Auth\AgentLogoutController::class, 'logout'])->name('agent.logout');

// 求人を更新する！
Route::middleware('auth:master_agent')->prefix('agent')->group(function () {
    Route::get('jobs/edit/{order_code}', [AgentJobController::class, 'showEditJobPage'])->name('agent.agentJobEdit');
    Route::put('jobs/update/{order_code}', [AgentJobController::class, 'updateJob'])->name('agent.update');
});

// Route::get('/agent/{orderCode}/agentJobEdit', [AgentJobController::class, 'showEditJobPage'])->name('agent.agentJobEdit');
// Route::put('/agent/{orderCode}', [AgentJobController::class, 'updateJob'])->name('agent.update');
// 採用企業の方へ
Route::get('/agent/create_company', [MasterCompanyController::class, 'showForm'])->name('agent.create_company')->middleware('auth:master_agent');
Route::post('/agent/create_company/store', [MasterCompanyController::class, 'store'])->name('create_company.store')->middleware('auth:master_agent');

Route::get('/agent/company/{companyCode}/detail', [AgentDashboardController::class, 'showCompanyDetail'])->name('agent.company.detail');
Route::get('/agent/company/{companyCode}/create-job', [AgentJobController::class, 'showCreateJobPage'])->name('agent.create_job');
Route::post('/agent/company/{companyCode}/create-job', [AgentJobController::class, 'storeJob'])->name('agent.store_job');

//オファー
// // ✅ Bitta GET so‘rov uchun
// Route::get('/offer.regist', [OfferController::class, 'registOffer'])->name('offer.regist');

// // ✅ POST uchun boshqa nom bering
// Route::post('/offer/regist/{id}', [OfferController::class, 'registOffer'])->name('offer.regist.submit');

// ✅ GET のルート（ユーザーがページにアクセスするためのルート）
Route::get('/offer/regist/{id}', [OfferController::class, 'showOfferForm'])->name('offer.regist');

// ✅ POST のルート（ユーザーがフォームを送信できるようにするため）
Route::post('/offer/regist/{id}', [OfferController::class, 'registOffer'])->name('offer.regist.submit');
// Route::get('/offer-completion', [OfferController::class, 'completion'])->name('offer.completion');
Route::get('/offer-completion', [OfferController::class, 'completion'])->name('offer-completion');

//オファーキャンセル
// Route::get('/offer.camcel', [OfferController::class, 'cancelOffer'])->name('offer.cancel');
// Route::post('/offer/cancel/{id}', [OfferController::class, 'cancelOffer'])->name('offer.cancel.submit');
Route::post('/agent/offer/complete/{staff_code}/{order_code}', [AgentDashboardController::class, 'confirmOfferCompletion'])
    ->name('agent.confirmOfferCompletion');
Route::get('/agent/user-details/{staff_code}', [AgentDashboardController::class, 'getUserDetail'])
    ->name('agent.userDetail');

// 🔹 募集開始 (Ishni boshlash) va 募集の一時停止 (Ishni to'xtatish) 
// Route::post('/jobs/pause', [CompanyDashboardController::class, 'pauseJob'])->name('jobs.pause');
// Route::post('/jobs/start', [CompanyDashboardController::class, 'startJob'])->name('jobs.start');
// ✅ 会社専用のルート
Route::middleware('auth:master_company')->group(function () {
    Route::post('/company/jobs/pause', [CompanyDashboardController::class, 'pauseJob'])->name('jobs.pause');
    Route::post('/company/jobs/start', [CompanyDashboardController::class, 'startJob'])->name('jobs.start');
});
// 📄 エージェント求人 public_limit_day 2週間延長
Route::post('/agent/extend-public-limit', [AgentDashboardController::class, 'extendPublicLimit'])
    ->name('agent.extend_public_limit');
Route::post('/agent/jobs/extend-limit', [AgentJobController::class, 'extendPublicLimit'])->name('agent.jobs.extendLimit');

//✅ エージェント用の別ルート
Route::middleware(['auth:master_agent'])->group(function () {
    Route::post('/jobs/pause', [AgentDashboardController::class, 'pauseJob'])->name('agent.jobs.pause');
    Route::post('/jobs/start', [AgentDashboardController::class, 'startJob'])->name('agent.jobs.start');
});

// エージェントオファー管理
Route::middleware(['auth:master_agent'])->group(function () {
    Route::get('/agent/offercontrol', [AgentDashboardController::class, 'getOfferDetail'])->name('agent.offercontrol');
    Route::post('/agent/confirmCancelOffer/{staff_code}/{order_code}', [AgentDashboardController::class, 'confirmCancelOffer'])->name('agent.confirmCancelOffer');
});

//履歴書
Route::get('/export', [App\Http\Controllers\ExportController::class, 'export'])->name('export');

Route::get('/pdf', [App\Http\Controllers\ExportController::class, 'pdf'])->name('pdf');

//職務経歴書
Route::get('/careersheet', [App\Http\Controllers\ExportController::class, 'careersheet'])->name('careersheet');

Route::get('/careerpdf', [App\Http\Controllers\ExportController::class, 'careerpdf'])->name('careerpdf');

Route::get('/contact', [ContactController::class, 'showContactForm'])->name('contact.form');
Route::post('/staff-contact', [ContactController::class, 'sendStaffContactEmail'])->name('sendcontact.staff');

Route::get('/company_contact', [ContactController::class, 'showCompanyContactForm'])->name('company_contact.form');
Route::post('/company-contact', [ContactController::class, 'sendCompanyContactEmail'])->name('sendcontact.company');

// Anketa sahifasini ko‘rsatish//申し込みページを表示
Route::get('/questionnaire', [ContactController::class, 'showForm'])->name('questionnaire');

// Anketani yuborish //アンケートを送信する
Route::post('/questionnaire/submit', [ContactController::class, 'submitForm'])->name('questionnaire.submit');

//csvupload
Route::get('/csvupload', [CsvUploadController::class, 'showForm'])->name('csvupload.form');
Route::post('/uploadcsv', [CsvUploadController::class, 'uploadCsv'])->name('csvupload.upload');

//agent user search// org get
// Route::match(['GET', 'POST'], 'agent/user_search', [AgentDashboardController::class, 'showUserForm'])->name('usersearch.form');
// Route::match(['GET', 'POST'], 'agent/usersearch', [AgentDashboardController::class, 'searchUser'])->name('agent.usersearch');
// 検索フォーム表示
Route::get('agent/usersearch', [AgentDashboardController::class, 'showUserForm'])->name('agent.usersearch.form');

// 検索処理実行 (POST)
Route::post('agent/usersearch', [AgentDashboardController::class, 'searchUser'])->name('agent.usersearch');

// 🆕 検索結果一覧ページ
Route::get('agent/search-user-list', [AgentDashboardController::class, 'showSearchUserList'])->name('agent.search_user_list');

// スタッフ詳細ページ
Route::get('agent/user-details/{staff_code}', [AgentDashboardController::class, 'getUserDetail'])->name('agent.userDetail');

// 20250317
Route::post('agent/listuser', [AgentDashboardController::class, 'listUser'])->name('agent.listuser');
//daylyExcel
Route::post('agent/dailysheet', [AgentDashboardController::class, 'dailySheet'])->name('agent.dailysheet');

//Route::get('/debug-env', function () {
//    $output = [];
//    exec('env', $output);
//    return response()->json($output);
//});
