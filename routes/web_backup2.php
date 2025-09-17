<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyRegisterController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\MatchingsController;
use App\Http\Controllers\ProfileController;

// メインページ
Route::get('/', function () {
    return view('welcome');
});


// ログイン用
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



// サインイン
Route::get('/signin', [RegisterController::class, 'showEmailCreate'])->name('signin');
//採用企業の方へ
Route::get('/company_create', [CompanyRegisterController::class, 'showRegisterForm'])->name('company_create');
Route::post('/create_company', [CompanyRegisterController::class, 'store'])->name('create_company.store');
Route::get('/company_page', [CompanyRegisterController::class, 'CompanyPage'])->name('company_page');

// 登録するには
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::get('/register', [RegisterController::class, 'register'])->name('register.get');

// メール確認ページへ
Route::get('/verify-email/{token}', [RegisterController::class, 'verifyEmail'])->name('verify.email');

// フルネームとパスワードの入力ページを表示する
Route::get('/set-password', [RegisterController::class, 'showSetPasswordForm'])->name('set-password');
Route::post('/set-password', [RegisterController::class, 'setPassword'])->name('set-password.post');

// 認証後のユーザーページ (Middleware bilan himoyalangan marshrutlar)
Route::middleware('auth')->group(function () {
    Route::get('/mypage', [MypageController::class, 'myPage'])->name('mypage');
    Route::get('/matchings/create', [MatchingsController::class, 'create'])->name('matchings.create');
    Route::post('/matchings', [MatchingsController::class, 'store'])->name('matchings.store');
    Route::get('/profile/profile', [ProfileController::class, 'profile'])->name('profile.profile');
    Route::get('/matchings/results', [MatchingsController::class, 'showMatchingResults'])->name('matchings.results');
});
Route::get('/example', function () {
    return 'Success!';
})->middleware('check.something');
http://13.230.229.156/matchings/create
